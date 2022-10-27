<?php

declare(strict_types=1);

namespace App\Repository;

final class MatchRepository extends BaseRepository
{   
    public function get(string $from= null, string $to= null, string $date = null) : ?array {
        if($from && $to){
            $this->db->where('date_start', array($from, $to), 'BETWEEN');    
        }
        else if($date){
            $this->db->where('DATE(date_start) = "'.$date.'"');    
        }
        $this->db->orderBy('date_start', 'ASC');
        return $this->db->get('matches');
    }

    public function getOne(int $matchId, bool $is_external_id = false) : ?array {
        $column = !!$is_external_id ? 'ls_id' : 'id';
        $this->db->where($column, $matchId);
        return $this->db->getOne('matches');
    }

    public function create(int $ls_id, int $league_id, ?int $home_id, ?int $away_id, int $round, string $date_start, string $match_ls_suffix = null){
        $data = array(
			"ls_id" => $ls_id,
			"league_id" => $league_id,
			"home_id" => $home_id,
			"away_id" => $away_id,
			"round" => $round,
			"date_start" => $date_start,
            "ls_suffix" => $match_ls_suffix
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

    public function updateTeams(int $id, int $home_id, int $away_id){
        $data = array(
			"home_id" => $home_id,
			"away_id" => $away_id,
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

    public function getMatchesForLeagues(
        array $league_ids, 
        ?int $from_days_diff = null, 
        ?int $until_days_diff = null, 
        ?string $sort = 'ASC', 
        ?int $limit = 50
    ) : ?array {

        $start = !is_null($from_days_diff) ? date("Y-m-d H:i:s", strtotime('+'.$from_days_diff.'days')) : null;
        $finish = !is_null($until_days_diff) ? date("Y-m-d H:i:s", strtotime('+'.$until_days_diff.'days')) : null;

        if($start && $finish){
            $this->db->where('date_start', array($start, $finish), 'BETWEEN');    
        }
        else if($start){
            $this->db->where('date_start', $start, '>');    
        }
        else if($finish){
            $this->db->where('date_start', $finish, '<');    
        }
        
        //TODO add where league_id + round not distinc  i.e serie a only round 4, 
        $this->db->where('league_id', $league_ids, 'IN');
        $this->db->orderBy('date_start', $sort);
        
        return $this->db->get('matches', $limit);
    }

}
