<?php

declare(strict_types=1);

namespace App\Repository;

final class PPRankingRepository extends BaseRepository
{  
   /**
     * Fetches user rankings based on guess points from a specified timeframe.
     * 
     * @param string $fromTime A relative time string compatible with strtotime().
     * @return array An array of user rankings with total and average points.
     */
    public function fetchPointsFromGuesses(string $fromTime = '-13 week', ?int $userId = null): array
    {
        // Calculate the date from the relative time string.
        $dateFrom = date('Y-m-d H:i:s', strtotime($fromTime));

        // Prepare the database query with conditions and joins.
        $this->db->join("guesses g", "u.id = g.user_id", "LEFT");
        $this->db->where("g.verified_at", $dateFrom, '>='); // Ensure correct usage of the operator
        $this->db->groupBy("u.id");
        if($userId) $this->db->where('u.id', $userId);
        $this->db->orderBy("tot_points", "desc");

        // Select required fields.
        $select = "u.id AS user_id, SUM(g.points) AS tot_points, COUNT(g.id) AS num_guesses, IF(COUNT(g.id) = 0, 0, SUM(g.points) / COUNT(g.id)) AS avg_points";
        $result = $this->db->get("users u", null, $select);

        // Check for errors or empty result.
        if ($this->db->count === 0) {
            return [];
        }

        return $result;
    }

    /**
     * Fetches the league trophy points for users based on their positions.
     *
     * @param string $fromTime A relative time string compatible with strtotime().
     * @return array An array of user league trophy points.
     */
    public function fetchPointsFromPPLeagues(string $fromTime = '-13 week', ?int $userId = null): array
    {
        // Calculate the date from the relative time string.
        $dateFrom = date('Y-m-d H:i:s', strtotime($fromTime));

        // Prepare the database query with conditions, joins and selections.
        $this->db->join("ppTournamentTypes pptt", "pptt.id = ups.ppTournamentType_id", "INNER");
        $this->db->join("users u", "u.id = ups.user_id", "INNER");
        $this->db->join("ppLeagues ppl", "ups.ppLeague_id = ppl.id", "INNER");
        if($userId) $this->db->where('u.id', $userId);
        $this->db->where("ups.ppLeague_id IS NOT NULL");
        $this->db->where("ppl.finished_at IS NOT NULL");
        $this->db->where("ups.position", [1, 2, 3], "IN");
        $this->db->where("ups.updated_at ", $dateFrom, '>=');
        $this->db->groupBy("ups.user_id");
        $this->db->orderBy("tot_points", "desc");

        // Custom select for calculated trophy points.
        $select = "ups.user_id, group_concat(ups.id),
            SUM(CASE 
                WHEN ups.position = 1 THEN pptt.cost * 2
                WHEN ups.position = 2 THEN pptt.cost  
                WHEN ups.position = 3 THEN pptt.cost * 0.5
                ELSE 0
            END) AS tot_points
        ";
        
        $result = $this->db->get("userParticipations ups", null, $select);

        // Check for errors or empty result.
        if ($this->db->count === 0) {
            return [];
        }

        return $result;
    }


    /**
     * Fetches cup points based on user performances in various cup levels.
     *
     * @param string $fromTime A relative time string compatible with strtotime().
     * @return array An array of user cup points.
     */
    public function fetchPointsFromPPCups(string $fromTime = '-13 week', ?int $userId = null): array
    {
        // Calculate the date from the relative time string.
        $dateFrom = date('Y-m-d H:i:s', strtotime($fromTime));
        $params = [$dateFrom, $dateFrom];

        // SQL to execute with the CTE and main query combined.
        $sql = "
            WITH MaxLevels AS (
                SELECT 
                    ups.user_id,
                    ups.ppCup_id,
                    MAX(pcg.level) AS max_level
                FROM 
                    userParticipations ups
                INNER JOIN 
                    ppCupGroups pcg ON pcg.id = ups.ppCupGroup_id
                WHERE 
                    ups.ppCupGroup_id IS NOT NULL
                    AND pcg.finished_at >= ? 
                    AND pcg.finished_at IS NOT NULL
                    AND pcg.level IS NOT NULL
                GROUP BY 
                    ups.user_id, ups.ppCup_id
            ), HighestLevelRecords AS (
                SELECT 
                    ups.user_id,
                    ups.ppCup_id,
                    ups.ppCupGroup_id,
                    ups.ppTournamentType_id,
                    pcg.level,
                    ups.position,
                    pptt.cost,
                    pptt.cup_format
                FROM 
                    userParticipations ups
                INNER JOIN 
                    ppCupGroups pcg ON pcg.id = ups.ppCupGroup_id
                INNER JOIN 
                    ppTournamentTypes pptt ON pptt.id = pcg.ppTournamentType_id
                INNER JOIN 
                    MaxLevels ml ON ml.user_id = ups.user_id AND ml.ppCup_id = ups.ppCup_id AND ml.max_level = pcg.level
                WHERE 
                    ups.ppCupGroup_id IS NOT NULL
                    AND pcg.finished_at >= ? 
                    AND pcg.finished_at IS NOT NULL
                    AND pcg.level IS NOT NULL
            )
            SELECT 
                hlr.user_id,
                u.username,
                hlr.ppCup_id,
                hlr.ppCupGroup_id,
                hlr.ppTournamentType_id,
                hlr.level,
                hlr.position,
                hlr.cup_format,
                hlr.cost
            FROM 
                HighestLevelRecords hlr
            INNER JOIN 
                users u ON hlr.user_id = u.id
            WHERE hlr.level > 1
        ";
        
        // Dynamically add the user filter if userId is not null
        if ($userId !== null) {
            $sql .= " AND hlr.user_id = ?";
            $params[] = $userId;
        }

        $sql .= " ORDER BY hlr.user_id, hlr.ppCup_id;";

        $result = $this->db->rawQuery($sql, $params);

        if (empty($result)) {
            return [];
        }

        return $result;
    }

    public function saveRankings(array $rankings, string $date): void
    {
        $position = 1; // Start position counter.
        foreach ($rankings as $userId => $points) {
            $data = [
                'user_id' => $userId,
                'tot_points' => $points,
                'position' => $position++, // Increment position for each user.
                'calculated_at' => $date
            ];
            $this->db->insert('ppRankings', $data);
        }
    }


    /**
     * Fetches rankings by date.
     *
     * @param string $date
     * @return array
     */
    public function fetchRankingsByDate(
        ?string $date = null, 
        ?int $offset = null, 
        ?int $limit = 20,
    ): array {
  
       
    
        if ($date === null) {
            // If no date is provided, fetch the most recent ranking date
            $this->db->orderBy("ppRankings.calculated_at", "DESC");
            $mostRecent = $this->db->getOne('ppRankings', 'ppRankings.calculated_at');
            if(!$mostRecent)return[];
            $date = $mostRecent['calculated_at']; // Setting the date to the most recent available
        }

        $columns = [
            'ppRankings.user_id',
            'u.username',
            'ppRankings.position',
            'ppRankings.tot_points',
            'ppRankings.calculated_at'
        ];
    
    
        // Join with the users table to get the username
        $this->db->join("users u", "u.id = ppRankings.user_id", "INNER");
        // Fetch rankings for the specific or most recent date
        $this->db->where('ppRankings.calculated_at', $date);
        $rankings = $this->db->withTotalCount()->get('ppRankings', [$offset, $limit], $columns) ?: [];
        return [
            'ppRankings' => $rankings,
            'total' => (int) $this->db->totalCount,
        ];
    }

    public function fetchForUser(int $userId){
        $this->db->where('user_id', $userId);
        $this->db->orderBy('calculated_at');
        return $this->db->getOne('ppRankings');
    }

}

