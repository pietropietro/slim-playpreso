<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Match;

final class MatchRepository extends BaseRepository
{   
    public function getOne(int $matchId, bool $is_external_id) {
        $column = !!$is_external_id ? 'ls_id' : 'id';
        $this->getDb()->where($column, $matchId);
        return $this->getDb()->getOne('matches');
    }

    public function create(int $ls_id, int $league_id, int $home_id, int $away_id, int $round, string $date_start){
        $data = array(
			"ls_id" => $ls_id,
			"league_id" => $league_id,
			"home_id" => $home_id,
			"away_id" => $away_id,
			"round" => $round,
			"date_start" => $date_start,
	    );
        if(!$this->getDb()->insert('matches',$data)){
            throw new \App\Exception\Mysql($this->getDb()->getLastError(), 500);
        };
        return true;
    }

    public function updateDateStart(int $id, string $date_start){
        $data = array(
			"date_start" => $date_start,
            "rescheduled_at" => date("Y-m-d H:i:s")
	    );
        
        $this->getDb()->where('id', $id);
        $this->getDb()->update('matches', $data);
    }
}
