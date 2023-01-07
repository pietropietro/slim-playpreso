<?php

declare(strict_types=1);

namespace App\Repository;

use \App\Exception\NotFound;

final class PPLeagueRepository extends BaseRepository
{
    public function get(?array $ids = null, ?int $ppTournamentTypeId) {
        if($ids)$this->db->where('id', $ids, 'IN');
        if($ppTournamentTypeId)$this->db->where('ppTournamentType_id', $ppTournamentTypeId);

        $ppLeagues=$this->db->get('ppLeagues', 50);
        if (! $ppLeagues) {
            throw new NotFound('ppLeagues not found.', 404);
        }   
        return $ppLeagues;
    }

    function getOne(int $id){
        $this->db->where('id',$id);
        return $this->db->getOne('ppLeagues');
    }

    function create(int $typeId){
        $data = array(
			"ppTournamentType_id" => $typeId,
			"created_at" => $this->db->now(),
	    );
        return $this->db->insert('ppLeagues',$data);
    }

    function setStarted(int $id) {
        $data = array(
            "started_at" => $this->db->now(),
        );
        $this->db->where('id', $id);
        $this->db->update('ppLeagues', $data, 1);
    }

    public function setFinished(int $id){
        $data = array(
            "finished_at" => $this->db->now(),
        );
        $this->db->where('id', $id);
        $this->db->update('ppLeagues', $data, 1);
    }

    function getJoinable(int $typeId){
        $this->db->where('ppTournamentType_id', $typeId);
        $this->db->where('started_at IS NULL');
       
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

  

}