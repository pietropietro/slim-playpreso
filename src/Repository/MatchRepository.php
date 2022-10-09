<?php

declare(strict_types=1);

namespace App\Repository;

final class MatchRepository extends BaseRepository
{   
    public function get() : ?array {
        $start = date("Y-m-d H:i:s", strtotime('-5 days'));
        $finish = date("Y-m-d H:i:s", strtotime('+5 days'));
        $this->db->where('date_start', array($start, $finish), 'BETWEEN');
        $this->db->orderBy('date_start', 'ASC');
        return $this->db->get('matches');
    }

    public function getOne(int $matchId, bool $is_external_id = false) : ?array {
        $column = !!$is_external_id ? 'ls_id' : 'id';
        $this->db->where($column, $matchId);
        return $this->db->getOne('matches');
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
        if(!$this->db->insert('matches',$data)){
            throw new \App\Exception\Mysql($this->db->getLastError(), 500);
        };
        return true;
    }

    public function updateDateStart(int $id, string $date_start){
        $data = array(
			"date_start" => $date_start,
            "rescheduled_at" => $this->db->now()
	    );

        $this->db->where('id', $id);
        $this->db->update('matches', $data, 1);
    }

    public function verify(int $id, int $score_home, int $score_away){
        $data = array(
			"score_home" => $score_home,
			"score_away" => $score_away,
            "verified_at" => $this->db->now()
	    );
        $this->db->where('id', $id);
        $this->db->update('matches', $data, 1);
    }

    public function getNextMatchesForLeagues(array $league_ids, int $plus_days) : ?array{

        $start = date("Y-m-d H:i:s", strtotime('+1 days'));
        $finish = date("Y-m-d H:i:s", strtotime('+'.$plus_days.'days'));

        //TODO add where league_id + round not distinc  i.e serie a only round 4, 
        $this->db->where('league_id', $league_ids, 'IN');
        $this->db->where('date_start', array($start, $finish), 'BETWEEN');
        $this->db->orderBy('date_start', 'ASC');
        
        return $this->db->get('matches', 50);
    }


    
}
