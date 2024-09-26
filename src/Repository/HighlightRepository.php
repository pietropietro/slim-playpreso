<?php

declare(strict_types=1);

namespace App\Repository;

final class HighlightRepository extends BaseRepository
{

    public function getLatestFullPresoRound(?int $userId = null, ?int $limit = 5, ?string $from = null, ?string $to = null)
    {
        // Start the query
        $sql = "
            SELECT pr.id AS id, g.user_id, GROUP_CONCAT(g.id) AS guess_ids
            FROM ppRounds pr
            JOIN ppRoundMatches prm ON pr.id = prm.ppRound_id
            JOIN guesses g ON prm.id = g.ppRoundMatch_id
            WHERE g.PRESO = 1
        ";
        
        // Add optional WHERE clause for user_id if provided
        if ($userId !== null) {
            $sql .= " AND g.user_id = " . intval($userId);
        }
        
        // Add optional date range filtering if both from and to are provided
        if ($from !== null && $to !== null) {
            $sql .= " AND g.guessed_at BETWEEN '" . $this->db->escape($from) . "' AND '" . $this->db->escape($to) . "'";
        }
    
        // Continue with the GROUP BY, HAVING, and ORDER clauses
        $sql .= "
            GROUP BY pr.id, g.user_id
            HAVING COUNT(prm.id) = (
                SELECT COUNT(prm2.id)
                FROM ppRoundMatches prm2
                WHERE prm2.ppRound_id = pr.id
            )
            AND COUNT(prm.id) > 1
            ORDER BY pr.created_at DESC
        ";
        
        // If limit is provided, add it to the query
        if ($limit !== null && $limit > 0) {
            $sql .= " LIMIT " . intval($limit); // Append LIMIT directly
        }
    
        // Execute the query
        $result = $this->db->rawQuery($sql);
    
        return $result;
    }
    

    
    
    

}
