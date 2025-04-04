<?php

declare(strict_types=1);

namespace App\Repository;

use MysqliDb;

final class FlashRepository extends BaseRepository
{
   
    public function get(bool $verified = true, int $offset = 0, int $limit = 10){
        $this->db->where('flash', 1);
        $this->db->join('matches m', 'm.id=pprm.match_id', 'inner');
        $this->db->where('verified_at is not null');
        //unnecessary but double condition
        $this->db->where('date_start < now()');
        $this->db->orderBy('date_start', 'desc');
        return $this->db->get('ppRoundMatches pprm', [$offset, $limit], 'pprm.*');
    }


    /**
     * Get the last (max date_start) flash match for a given date (YYYY-mm-dd).
     * or the last in general if none is provided
     * If none found, return null.
     *
     * @param  string $dateString e.g. "2025-01-19" (must be YYYY-mm-dd for DATE() comparison)
     * @return array|null
     */
    public function getLastFlash(?string $dateString = null, ?bool $verified = null): ?array
    {
        $this->db->join('matches m', 'pprm.match_id = m.id', 'INNER');
        $this->db->where('pprm.flash', 1);
    
        if ($dateString !== null) {
            // Filter by DATE(m.date_start) = $dateString
            // This might need rawWhere if MariaDB is picky.
            $this->db->where('DATE(m.date_start)', $dateString);
        }

        if ($verified === true) {
            $this->db->where('m.verified_at is not null');
        } else if ($verified === false) {
            $this->db->where('m.verified_at is null');
        }
    
        $this->db->orderBy('m.date_start', 'DESC');
        $row = $this->db->getOne('ppRoundMatches pprm', 'pprm.*');
    
        return $row ?: null;
    }

    /**
     * Get the next upcoming Flash match: date_start > NOW(), verified_at IS NULL
     */
    public function getNextFlash(): ?array
    {
        $this->db->join('matches m', 'pprm.match_id = m.id', 'INNER');
        $this->db->where('pprm.flash', 1);
        $this->db->where('m.verified_at is null');
        $this->db->where('m.date_start > now()');
        $this->db->orderBy('m.date_start', 'ASC');

        $row = $this->db->getOne('ppRoundMatches pprm', 'pprm.*');
        return $row ?: null;
    }


    /**
     * Get the currently in-progress Flash match:  verified_at IS NULL
     * 
     * Typically the "most recently started" or "still ongoing."
     */
    public function getCurrentFlash(): ?array
    {
        $this->db->join('matches m', 'pprm.match_id = m.id', 'INNER');
        $this->db->where('pprm.flash', 1);
        $this->db->where('m.verified_at is null');
        $this->db->where('now() > m.date_start');

        $dateAgo = date("Y-m-d H:i:s", strtotime('-3 hours'));
        $this->db->where('m.date_start', $dateAgo, '>');

        // Possibly we want the one that started most recently
        $this->db->orderBy('m.date_start', 'desc');
        $row = $this->db->getOne('ppRoundMatches pprm', 'pprm.*');

        return $row ?: null;
    }

    
    /**
     * Insert a new row into ppRoundMatches with flash=1, referencing the given match_id.
     *
     * @param int $matchId
     * @return int|null The new inserted ID on success, or null on failure
     */
    public function addFlashMatch(int $matchId, int $cost): ?int
    {
        $data = [
            'match_id'   => $matchId,
            'flash'      => 1,
            'lock_cost'  => $cost
        ];

        $newId = $this->db->insert('ppRoundMatches', $data);
        if ($newId === false) {
            // Insert failed; handle or log the error
            return null;
        }
        return $newId;
    }

    /**
     * Remove all flash matches for a given date.
     * This uses rawQuery because multi-table DELETE with JOIN is simpler that way.
     *
     * @param string $dateString "YYYY-mm-dd"
     */
    public function removeFlashByDate(string $dateString): void
    {
        // In MySQL/MariaDB, you can do:
        //   DELETE pprm
        //   FROM ppRoundMatches pprm
        //   JOIN matches m ON pprm.match_id = m.id
        //   WHERE pprm.flash = 1
        //     AND DATE(m.date_start) = ?
        //
        $sql = "
            DELETE pprm
            FROM ppRoundMatches pprm
            JOIN matches m ON pprm.match_id = m.id
            WHERE pprm.flash = 1
              AND DATE(m.date_start) = ?
        ";
        $this->db->rawQuery($sql, [$dateString]);
    }

    /**
     * Get all flash matches for a specific date (YYYY-mm-dd).
     */
    public function getFlashMatchesByDate(string $dateString): array
    {
        $this->db->join('matches m', 'pprm.match_id = m.id', 'INNER');
        $this->db->where('pprm.flash', 1);
        // Filter by date; might need rawWhere or from/to if strict
        $this->db->where('DATE(m.date_start)', $dateString);
        return $this->db->get('ppRoundMatches pprm', null, 'pprm.*');
    }

    public function getWithMatch(int $matchId){
        $this->db->where('flash', 1);
        $this->db->where('match_id', $matchId);
        return $this->db->getOne('ppRoundMatches');
    }

    public function retrieveFlashChart(?int $offset = 0, ?int $limit = 10) {
        $dateAgo = date("Y-m-d", strtotime('-30 days'));
        
        $this->db->join("ppRoundMatches pprm", "pprm.id = guesses.ppRoundMatch_id", "INNER");
        $this->db->where("guesses.created_at", $dateAgo, ">=");
        $this->db->where('flash', 1);
        $this->db->where('verified_at is not null');
        $this->db->groupBy("guesses.user_id");
        
        // Order by tot_wins, net_prize, and tot_points
        $this->db->orderBy("tot_wins", "desc");
        $this->db->orderBy("net_prize", "desc");
        $this->db->orderBy("tot_points", "desc");
    
        $chart = $this->db->withTotalCount()->get(
            "guesses", 
            [$offset, $limit], 
            "   guesses.user_id, 
                SUM(pprm.lock_cost) as tot_lock_cost,
                SUM(guesses.winner) as tot_wins,
                COALESCE(SUM(guesses.winner_prize), 0) as tot_prize, 
                (COALESCE(SUM(guesses.winner_prize), 0) - SUM(pprm.lock_cost)) as net_prize,
                SUM(guesses.points) as tot_points,
                COUNT(guesses.id) as tot_locked,
                SUM(PRESO) as tot_preso, 
                SUM(UNOX2) as tot_unox2"
        );        
        
        return [
            'chart' => $chart,
            'total' => (int) $this->db->totalCount,
        ];
    }

    public function insertLeader(int $userId, int $points, int $after_pprm_id){
        $data = array(
            'user_id' => $userId,
            'tot_points' => $points,
            'after_pprm_id' => $after_pprm_id,
            'calculated_at' => $this->db->now()
        );
        return $this->db->insert('flashLeader', $data);
    }

    public function getFlashLeader(){
        $this->db->orderBy('calculated_at', 'desc');
        return $this->db->getOne('flashLeader');
    }

}


