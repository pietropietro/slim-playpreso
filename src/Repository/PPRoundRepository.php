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
}
