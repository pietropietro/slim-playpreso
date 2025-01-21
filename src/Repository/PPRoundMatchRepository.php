<?php

declare(strict_types=1);

namespace App\Repository;

final class PPRoundMatchRepository extends BaseRepository
{
    public function getOne(int $id){
        $this->db->where('id', $id);
        return $this->db->getOne('ppRoundMatches');
    }

    public function getForRound(int $ppRoundId, ?bool $onlyIds = false) : array {
        $this->db->where('ppRound_id', $ppRoundId);
        if($onlyIds)return $this->db->getValue('ppRoundMatches', 'id', null);
        return $this->db->get('ppRoundMatches');
    }


    public function getMatchIdsForRound(int $ppRoundId){
        $this->db->where('ppRound_id', $ppRoundId);
        return $this->db->getValue('ppRoundMatches', 'match_id', null);
    }

    public function getMatchId(int $id){
        $this->db->where('id', $id);
        return $this->db->getValue('ppRoundMatches','match_id');
    }

    public function getRoundIdsForMatches(array $matchIds) {
        $this->db->where('match_id', $matchIds, 'IN');
        $this->db->setQueryOption ('DISTINCT');
        return $this->db->getValue('ppRoundMatches', 'ppRound_id', null);
    }

    public function create(int $matchId, int $ppRoundId, ?int $lock_cost=null){
        $data = array(
			"ppRound_id" => $ppRoundId,
            "match_id" => $matchId,
            "lock_cost" => $lock_cost
	    );

        if(!$this->db->insert('ppRoundMatches',$data)){
            throw new \App\Exception\Mysql($this->db->getLastError(), 500);
        };
        
        return $this->db->getInsertId();
    }

    public function changeMatch(int $id, int $newMatchId){
        $data = array(
            "match_id" => $newMatchId
        );
        $this->db->where('id', $id);
        $this->db->update('ppRoundMatches', $data);  
    }

    

    public function delete(int $id){
        if(!$id) return;
        $this->db->where('id', $id);
        return $this->db->delete('ppRoundMatches',1);
    }

}
