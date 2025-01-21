<?php

declare(strict_types=1);

namespace App\Service\Flash;

use App\Service\BaseService;
use App\Repository\MatchRepository;
use App\Repository\FlashRepository;

final class Create extends BaseService
{
    public function __construct(
        protected FlashRepository $flashRepository,
        protected MatchRepository $matchRepository,
    ){}

    /**
     * The main entry point:
     * Pick all flash matches for a given date (DD-mm-YYYY).
     *
     * 1. Parse the requested date.
     * 2. Determine earliest time to pick (based on previous day's last flash).
     * 3. Get all candidate matches in [earliest..endOfDay].
     * 4. De-duplicate by date_start (random pick if multiple).
     * 5. Greedily pick matches spaced 135min apart.
     * 6. Insert them into ppRoundMatches (flash=1).
     *
     * @param string $pickedDate in format "d-m-Y", e.g. "24-02-2025"
     * @return int[]  IDs of matches that were chosen as flash
     */
    public function pickForDate(string $pickedDate): array
    {
        // (1) parse the date into \DateTime, get "YYYY-mm-dd"
        $dateObj = $this->parseDate($pickedDate);
        $pickedIso = $dateObj->format('Y-m-d');

        // (2) figure out earliest time, including offset from previous day's last flash
        $earliest = $this->calculateEarliestTime($dateObj);

        // (3) figure out end of day for $pickedIso
        $latest = $this->getEndOfDay($dateObj);

        // (4) retrieve candidate matches from db
        $matches = $this->getCandidateMatches($earliest, $latest);

        // (5) group by date_start and pick randomly from duplicates
        $uniqueMatches = $this->groupMatchesByDateStart($matches);

        // (6) apply the 2h15m (135min) greedy selection
        $chosen = $this->greedyPick($uniqueMatches);

        // (7) insert them into ppRoundMatches with flash=1
        $chosenIds = $this->saveFlashMatches($chosen);

        return $chosenIds;
    }

    /**
     * Parse d-m-Y into a DateTime, or throw.
     */
    private function parseDate(string $pickedDate): \DateTime
    {
        $dateObj = \DateTime::createFromFormat('d-m-Y', $pickedDate);
        if (!$dateObj) {
            throw new \InvalidArgumentException("Invalid date format: $pickedDate (expected d-m-Y)");
        }
        return $dateObj;
    }

    /**
     * Calculate earliest time we can pick for a given day
     * based on the last flash of the previous day + 135 minutes.
     */
    private function calculateEarliestTime(\DateTime $dateObj): \DateTime
    {
        // day in ISO
        $pickedIso = $dateObj->format('Y-m-d');

        // get previous day in ISO
        $prevDateObj = clone $dateObj;
        $prevDateObj->modify('-1 day');
        $prevIso = $prevDateObj->format('Y-m-d');

        // get last flash from previous day
        $lastFlashRow = $this->flashRepository->getLastFlash($prevIso);

        // default earliest is midnight of the day in question
        $earliest = new \DateTime($pickedIso . ' 00:00:00');

        if ($lastFlashRow) {
            // lastFlashRow['date_start'] is e.g. "2025-01-18 23:00:00"
            $prevMatchTime = new \DateTime($lastFlashRow['date_start']);
            $prevMatchTime->modify('+135 minutes'); // add 2h15m

            if ($prevMatchTime > $earliest) {
                $earliest = $prevMatchTime;
            }
        }

        return $earliest;
    }

    /**
     * Get the end of day (23:59:59) for a given date object.
     */
    private function getEndOfDay(\DateTime $dateObj): \DateTime
    {
        $pickedIso = $dateObj->format('Y-m-d');
        return new \DateTime($pickedIso . ' 23:59:59');
    }

    /**
     * Retrieve matches in range [earliest..latest] using your MatchRepository->adminGet or similar.
     */
    private function getCandidateMatches(\DateTime $earliest, \DateTime $latest): array
    {
        // We assume adminGet can filter 'date_start >= $earliest' and 'date_start <= $latest'
        return $this->matchRepository->adminGet(
            from: $earliest->format('Y-m-d H:i:s'),
            to:   $latest->format('Y-m-d H:i:s')
        );
    }

    /**
     * If multiple matches have the same date_start, pick randomly one from each group.
     */
    private function groupMatchesByDateStart(array $matches): array
    {
        // group by date_start
        $grouped = [];
        foreach ($matches as $m) {
            $ds = $m['date_start'];
            $grouped[$ds][] = $m;
        }

        // pick one per date_start
        $uniqueMatches = [];
        foreach ($grouped as $ds => $arr) {
            if (count($arr) === 1) {
                $uniqueMatches[] = $arr[0];
            } else {
                // pick random
                $uniqueMatches[] = $arr[array_rand($arr)];
            }
        }

        // sort by date_start ascending
        usort($uniqueMatches, fn($a, $b) => strcmp($a['date_start'], $b['date_start']));

        return $uniqueMatches;
    }

    /**
     * Greedy pick: if the current match is >= lastChosen + 135 min, accept it.
     */
    private function greedyPick(array $uniqueMatches): array
    {
        $chosen = [];
        $lastChosenTime = null; // track the last accepted match time

        foreach ($uniqueMatches as $m) {
            $currTime = new \DateTime($m['date_start']);
            if ($lastChosenTime === null) {
                // first match, accept
                $chosen[] = $m;
                $lastChosenTime = $currTime;
            } else {
                // only accept if >= last + 135 min
                $nextAllowed = (clone $lastChosenTime)->modify('+135 minutes');
                if ($currTime >= $nextAllowed) {
                    $chosen[] = $m;
                    $lastChosenTime = $currTime;
                }
            }
        }

        return $chosen;
    }

    /**
     * Insert the chosen matches into ppRoundMatches with flash=1,
     * and return an array of their match IDs.
     */
    private function saveFlashMatches(array $chosen): array
    {
        $chosenIds = [];
        foreach ($chosen as $match) {
            $matchId = (int) $match['id'];
            
            // 1) Decide the lock_cost. Let's pick randomly from [10, 20, 50, 100].
            $possibleCosts = [20, 50, 100];
            $cost = $possibleCosts[array_rand($possibleCosts)];

            $this->flashRepository->addFlashMatch($matchId, $cost);
            $chosenIds[] = $matchId;
        }
        return $chosenIds;
    }

}
