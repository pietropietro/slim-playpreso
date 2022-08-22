<?php

declare(strict_types=1);

namespace App\Repository;

use \App\Exception\NotFound;

final class PPLeagueRepository extends BaseRepository
{
    public function get(array $ids) {
        $this->db->where('id', $ids, 'IN');
        $ppLeagues=$this->db->get('ppLeagues');
        if (! $ppLeagues) {
            throw new NotFound('ppLeagues not found.', 404);
        }   
        return $ppLeagues;
    }

    function getJoinable(int $typeId){
        $this->db->where('ppLeagueType_id', $typeId);
        $this->db->where('started_at IS NULL');
        $this->db->where('user_count', 20, "<");
       
        return $this->db->getOne('ppLeagues');
    }

    function getOne(int $id){
        $this->db->where('id',$id);
        return $this->db->getOne('ppLeagues');
    }


    public function startedIds(){
        $this->db->where('started_at IS NOT NULL');
        return $this->db->getValue('ppLeagues', 'id', null);
    }
    
    function updateValue(int $id, string $column, $value){
        $data = array(
            $column => $value,
        );
        $this->db->where('id',$id);
        $this->db->update('ppLeagues', $data);
    }

    function create(int $typeId){
        $data = array(
			"ppLeagueType_id" => $typeId,
			"created_at" => date("Y-m-d H:i:s"),
			"user_count" => 0,
            "round_count" => 0
	    );
        return $this->db->insert('ppLeagues',$data);
    }

    function incUserCount(int $id){
        $this->db->query("UPDATE ppLeagues SET user_count=user_count+1 WHERE id=$id");
    }

    function incRoundCount(int $id) {
        $data = array(
            "round_count" => $this->db->inc()
        );
        $this->db->where('id', $id);
        $this->db->update('ppLeagues', $data, 1);
    }
}