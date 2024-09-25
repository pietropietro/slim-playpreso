<?php

declare(strict_types=1);

namespace App\Repository;

final class HighlightRepository extends BaseRepository
{

    public function getLatestFullPresoRound(?int $userId = null, ?int $limit = 5)
    {
        // Start the base SQL query
        $sql = "SELECT pr.id AS ppRound_id, g.user_id, group_concat(g.id) as guess_ids
                FROM ppRounds pr
                JOIN ppRoundMatches prm ON pr.id = prm.ppRound_id
                JOIN guesses g ON prm.id = g.ppRoundMatch_id
                WHERE g.PRESO = 1";

        // If userId is provided, append it to the WHERE clause directly
        if ($userId !== null) {
            $sql .= " AND g.user_id = " . intval($userId); // Safely append the userId to the SQL
        }

        // Add the GROUP BY and HAVING clauses
        $sql .= " GROUP BY pr.id, g.user_id
                HAVING COUNT(prm.id) = (
                    SELECT COUNT(prm2.id)
                    FROM ppRoundMatches prm2
                    WHERE prm2.ppRound_id = pr.id
                )
                AND COUNT(prm.id) > 1  -- Only consider rounds with more than one match
                ORDER BY pr.created_at DESC";

        // If limit is provided, append it to the SQL query directly
        if ($limit !== null && $limit > 0) {
            $sql .= " LIMIT " . intval($limit); // Safely append the limit to the SQL
        }

        // Debug: see the final SQL query
        // var_dump($sql);

        // Execute the raw query (no parameter binding needed since it's directly injected)
        $result = $this->db->rawQuery($sql);

        return $result;
    }

    
    
    

}
