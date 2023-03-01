<?php

declare(strict_types=1);

namespace App\Repository;

final class PPRoundMatchRepository extends BaseRepository
{
    public function getForRound(int $ppRoundId, ?bool $onlyIds = false) : array {
        $this->db->where('ppRound_id', $ppRoundId);
        if($onlyIds)return $this->db->getValue('ppRoundMatches', 'id', null);
        return $this->db->get('ppRoundMatches');
    }

    public function getMatchIdsForRound(int $ppRoundId){
        $this->db->where('ppRound_id', $ppRoundId);
        return $this->db->getValue('ppRoundMatches', 'match_id', null);
    }

    public function getRoundIdsForMatches(array $matchIds) {
        $this->db->where('match_id', $matchIds, 'IN');
        $this->db->setQueryOption ('DISTINCT');
        return $this->db->getValue('ppRoundMatches', 'ppRound_id', null);
    }

    public function getCurrentMotd(){
        $this->db->join('guesses g', 'pprm.id = g.ppRoundMatch_id', 'LEFT');
        $this->db->groupBy('pprm.id');
        $this->db->orderBy('motd');
        
        if(date('H',time())<7){
            $this->db->where('motd', date('Y-m-d'), '<');
        }

        $this->db->where('motd is not null');
        return $this->db->getOne('ppRoundMatches pprm', 'pprm.*, count(g.id) as aggr_count');
    }

    public function getMotd(?string $dateString = null){
        $dateString = $dateString ?? date('Y-m-d');
        $this->db->where('motd', $dateString);
        return $this->db->getOne('ppRoundMatches');
    }
    
    public function hasMotd(?string $dateString = null){
        $dateString = $dateString ?? date('Y-m-d');
        $this->db->where('motd', $dateString);
        return $this->db->has('ppRoundMatches');
    }

    public function create(int $matchId, ?int $ppRoundId = null){
        $data = array(
			"ppRound_id" => $ppRoundId,
            "match_id" => $matchId
	    );

        if(!$ppRoundId){
            $this->db->where('id', $matchId);
            $match = $this->db->getOne('matches');
            $dateString = (new \DateTime($match['date_start']))->format('Y-m-d');
            $data["motd"] = $dateString;
        }

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

    public function getParentPPRound(int $id){
        $this->db->where('ppRoundMatches.id', $id);
        $this->db->join('ppRounds ppr', 'ppr.id=ppRoundMatches.ppRound_id');
        return $this->db->getOne('ppRoundMatches', 'ppr.*');
    }

    public function delete(int $id){
        if(!$id) return;
        $this->db->where('id', $id);
        return $this->db->delete('ppRoundMatches',1);
    }

}
