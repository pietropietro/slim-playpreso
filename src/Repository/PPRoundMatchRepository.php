<?php

declare(strict_types=1);

namespace App\Repository;

final class PPRoundMatchRepository extends BaseRepository
{
    public function getForRound(int $ppRoundId) : array {
        $this->db->where('ppRound_id', $ppRoundId);
        return $this->db->get('ppRoundMatches');
    }

    public function getRoundIdsForMatches(array $matchIds) {
        $this->db->where('match_id', $matchIds, 'IN');
        $this->db->setQueryOption ('DISTINCT');
        return $this->db->getValue('ppRoundMatches', 'ppRound_id', null);
    }

    public function create($ppRoundId, $matchId){
        $data = array(
			"ppRound_id" => $ppRoundId,
            "match_id" => $matchId
	    );
        if(!$this->db->insert('ppRoundMatches',$data)){
            throw new \App\Exception\Mysql($this->db->getLastError(), 500);
        };
        return $this->db->getInsertId();
    }

}
