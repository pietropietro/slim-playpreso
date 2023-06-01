<?php

declare(strict_types=1);

namespace App\Repository;

use \App\Exception\NotFound;

final class PPLeagueRepository extends BaseRepository
{
    public function get(
        ?array $ids = null, 
        ?int $ppTournamentTypeId, 
        ?bool $finished=null,
        ?bool $started=null
    ) {
        if($ids)$this->db->where('id', $ids, 'IN');
        if($ppTournamentTypeId)$this->db->where('ppTournamentType_id', $ppTournamentTypeId);
        if(isset($finished)){
            if($finished)$this->db->where('finished_at is not null');
            else $this->db->where('finished_at is null');
        }
        if(isset($started)){
            if($started)$this->db->where('started_at is not null');
            else $this->db->where('started_at is null');
        }
        $ppLeagues=$this->db->get('ppLeagues', 100);
        return $ppLeagues;
    }

    public function getPaused(
        ?int $ppTournamentTypeId, 
    ) {
        $sql = 'SELECT ppl.*
            FROM ppLeagues ppl
            WHERE ppl.started_at IS NOT NULL
            AND ppl.finished_at IS NULL
            AND NOT EXISTS (
                SELECT 1
                FROM ppRounds ppr
                INNER JOIN ppRoundMatches pprm ON pprm.ppRound_id = ppr.id
                INNER JOIN matches m ON pprm.match_id = m.id
                WHERE ppr.ppLeague_id = ppl.id
                AND m.verified_at IS NULL
            )';
        return $this->db->query($sql);
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