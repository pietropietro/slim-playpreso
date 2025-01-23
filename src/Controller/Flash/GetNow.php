<?php

declare(strict_types=1);

namespace App\Controller\Flash;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * GET /flash
 *
 * Returns a JSON structure with:
 *   - flash_next (the soonest upcoming)
 *   - flash_current (the one in progress if any)
 *   - flash_last (the most recently finished)
 */
final class GetNow extends Base
{
    /**
     * Handle the route: GET /flash
     *
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        // 1) Identify the user (from JWT or session, etc.)
        $userId = $this->getAndValidateUserId($request);

        // 2) Retrieve next/current/last flash matches
        //    (Implement these queries in your Flash\Find service)
        $flashNext    = $this->getFlashFindService()->getNextFlash($userId);
        $flashCurrent = $this->getFlashFindService()->getCurrentFlash($userId);
        $flashLast    = $this->getFlashFindService()->getLastFlash(dateString: null, verified: true, userId: $userId);

        // 3) For each match, prepare the "guess" data for the user
        $flashNext    = $this->prepareFlashItem($flashNext, $userId);
        $flashCurrent = $this->prepareFlashItem($flashCurrent, $userId);
        $flashLast    = $this->prepareFlashItem($flashLast, $userId);

        // 4) Return all three in a single response
        $returnArray = [
            'next'    => $flashNext,
            'current' => $flashCurrent,
            'last'    => $flashLast,
        ];

        return $this->jsonResponse($response, 'success', $returnArray, 200);
    }

    /**
     * Helper method: If there's a Flash match row, attach the user's guess or a "dummy" guess,
     * plus total locks (guesses).
     */
    private function prepareFlashItem(?array $pprmFlash, int $userId): ?array
    {
        if (!$pprmFlash) {
            return null; // No match found, return null
        }

        // Filter guesses for the same userId from the provided `pprmFlash` array
        $userGuess = array_filter($pprmFlash['guesses'] ?? [], function ($guess) use ($userId) {
            return $guess['user_id'] === $userId;
        });

        // If no guess exists for the user, build a dummy guess object
        if (empty($userGuess)) {
            $pprmFlash['guess'] = $this->getGuessCreateService()->buildDummyGuess($userId, $pprmFlash['id'], 'flash');
        } else {
            $pprmFlash['guess'] = reset($userGuess); // Use the first match
        }

        // Add the PPTournamentType to the guess
        $flashPPtt = $this->getPPTournamentTypeFindService()->getFlashPPTType();
        $pprmFlash['guess']['ppTournamentType'] = $flashPPtt;

        return $pprmFlash;
    }
}
