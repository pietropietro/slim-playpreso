<?php

declare(strict_types=1);

namespace App\Repository;

use MysqliDb;

final class FlashRepository extends BaseRepository
{
   
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
        $row = $this->db->getOne('ppRoundMatches pprm', null, 'm.*, pprm.id AS ppRoundMatchId');
    
        return $row ?: null;
    }

    /**
     * Get the next upcoming Flash match: date_start > NOW(), verified_at IS NULL
     */
    public function getNextFlash(): ?array
    {
        $this->db->join('matches m', 'pprm.match_id = m.id', 'INNER');
        $this->db->where('pprm.flash', 1);
        $this->db->where('m.verified_at', null);
        $this->db->where('m.date_start > now()');
        $this->db->orderBy('m.date_start', 'ASC');

        $cols = 'pprm.id AS ppRoundMatchId, m.*, pprm.lock_cost';
        $row = $this->db->getOne('ppRoundMatches pprm', null, $cols);
        return $row ?: null;
    }


    /**
     * Get the currently in-progress Flash match:  verified_at IS NULL
     * 
     * Typically the "most recently started" or "still ongoing."
     */
    public function getCurrentFlash(): ?array
    {
        $this->db->join('matches m', 'pm.match_id = m.id', 'INNER');
        $this->db->where('pm.flash', 1);
        $this->db->where('m.verified_at', null);
        // Possibly we want the one that started most recently
        $this->db->orderBy('m.date_start', 'DESC');

        $cols = 'pm.id AS ppRoundMatchId, m.*, pm.lock_cost';
        $row = $this->db->getOne('ppRoundMatches pm', null, $cols);
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
        $this->db->join('matches m', 'pm.match_id = m.id', 'INNER');
        $this->db->where('pm.flash', 1);
        // Filter by date; might need rawWhere or from/to if strict
        $this->db->where('DATE(m.date_start)', $dateString);
        return $this->db->get('ppRoundMatches pm', null, 'pm.id AS ppRoundMatchId, m.*');
    }

}


