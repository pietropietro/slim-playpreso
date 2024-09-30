<?php

declare(strict_types=1);

namespace App\Repository;

final class PPRoundRepository extends BaseRepository
{
    public function count(string $column, int $valueId) : int {
        $this->db->where($column,$valueId);
        $sql = "SELECT COUNT(*) as round_count FROM ppRounds";
        $result = $this->db->query($sql);
        return $result[0]['round_count'];
    }

    public function getForTournament(string $column, int $valueId, ?bool $only_last=false){
        $this->db->where($column, $valueId);
        if($only_last){
            $this->db->orderBy('round');
            return $this->db->getOne('ppRounds');
        }
        return $this->db->get('ppRounds');
    }

    public function getOne(int $id){
        $this->db->where('id', $id);
        return $this->db->getOne('ppRounds');
    }

    public function has(string $column, int $valueId, int $round) : bool{
        $this->db->where($column, $valueId);
        $this->db->where('round', $round);
        return $this->db->has('ppRounds');
    }

    public function create(string $column, int $valueId, int $round) : int{
        $data = array(
			$column => $valueId,
			"round" => $round,
	    );
        if(!$this->db->insert('ppRounds',$data)){
            throw new \App\Exception\Mysql($this->db->getLastError(), 500);
        };
        return $this->db->getInsertId();
    }

    public function getFromPPRM(int $ppRoundMatch_id){
        $this->db->join('ppRoundMatches pprm', 'pprm.ppRound_id=ppRounds.id', 'INNER');
        $this->db->where('pprm.id',$ppRoundMatch_id);
        return $this->db->getOne('ppRounds');
    }


    public function getFullPresoRound(?int $userId = null, ?int $limit = 5, ?string $from = null, ?string $to = null)
    {
        // Start the query
        $sql = "
            SELECT pr.id AS id, g.user_id
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
