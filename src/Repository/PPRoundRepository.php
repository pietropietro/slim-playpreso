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

    public function getForTournament($column, $valueId){
        $this->db->where($column, $valueId);
        return $this->db->get('ppRounds');
    }

    public function getOne(int $id){
        $this->db->where('id', $id);
        return $this->db->getOne('ppRounds');
    }

    public function create(string $column, int $valueId, int $round){
        $data = array(
			$column => $valueId,
			"round" => $round,
            "created_at" => $this->db->now()
	    );
        if(!$this->db->insert('ppRounds',$data)){
            throw new \App\Exception\Mysql($this->db->getLastError(), 500);
        };
        return $this->db->getInsertId();
    }
}
