<?php

declare(strict_types=1);

namespace App\Repository;

final class MatchRepository extends BaseRepository
{   
    private $whitelistColumns = array('id','league_id','home_id','away_id','score_home','score_away','round','date_start','verified_at');

    //TODO REFACTOR
    public function adminGet(array $ids=null, string $from= null, string $to= null, string $date = null) : ?array {
        if($from && $to){
            $this->db->where('date_start', array($from, $to), 'BETWEEN');    
        }
        else if($date){
            $this->db->where('DATE(date_start) = "'.$date.'"');    
        }

        if($ids) $this->db->where('m.id', $ids, 'IN');

        $this->db->join('guesses g', "g.match_id=m.id", "left");
        $this->db->join('ppRoundMatches pprm', "pprm.match_id=m.id", "left");

        $this->db->groupBy('m.id');
        $this->db->orderBy('date_start', 'ASC');

        $columns = array(
            'm.id', 'm.ls_id', 'm.league_id', 'm.home_id', 'm.away_id', 'm.score_home','m.score_away', 
            'm.round', 'm.date_start', 'm.created_at', 'm.verified_at', 'm.notes',
            // 'count(distinct g.ppRoundMatch_id) as ppRMcount', 
            'count(distinct pprm.id) as aggregatePPRM',
            'count(distinct pprm.motd) as motd',
            'count(g.id) as aggregateGuesses',
            'ROUND(sum(g.UNOX2)/count(guessed_at) * 100) as aggregateUNOX2',
            'ROUND(sum(g.GGNG)/count(guessed_at) * 100) as aggregateGGNG',
            'ROUND(sum(g.UO25)/count(guessed_at) * 100) as aggregateUO25',
            'ROUND(sum(g.PRESO)/count(guessed_at) * 100) as aggregatePRESO',
        );

        return $this->db->get('matches m', null, $columns);
    }

    public function get(array $ids){
        if(!$ids)return;
        $this->db->where('id', $ids, 'IN');
        return $this->db->get('matches');
    }

    public function getOne(int $matchId, bool $is_external_id = false) : ?array {
        $column = !!$is_external_id ? 'ls_id' : 'id';
        $this->db->where($column, $matchId);
        return $this->db->getOne('matches', $this->whitelistColumns);
    }

    public function getOneByLeagueRoundAndTeams(int $leagueId, int $round, int $homeId, int $awayId){
        $this->db->where('league_id', $leagueId);
        $this->db->where('round', $round);
        $this->db->where('home_id', $homeId);
        $this->db->where('away_id', $awayId);
        $this->db->where('verified_at IS NULL');
        $this->db->where('date_start > now()');
        $match= $this->db->getOne('matches');
        return $match;
    }


    public function getNextInPPRound(int $ppRound_id){
        $this->db->join('ppRoundMatches pprm','pprm.ppRound_id=ppRounds.id','INNER');
        $this->db->join('matches m','pprm.match_id=m.id','INNER');
        $this->db->where('ppRounds.id', $ppRound_id);
        $this->db->where('m.verified_at IS NULL');
        $this->db->orderBy('m.date_start','asc');
        return $this->db->getOne('ppRounds','pprm.id as pprm_id, m.*');
    }

    public function getNextInPPTournament(string $type, int $typeId){
        $this->db->join('ppRoundMatches pprm','pprm.ppRound_id=ppRounds.id','INNER');
        $this->db->join('matches m','pprm.match_id=m.id','INNER');
        $this->db->where('ppRounds.'.$type, $typeId);
        $this->db->where('m.verified_at IS NULL');
        $this->db->orderBy('m.date_start','asc');
        return $this->db->getOne('ppRounds','pprm.id as pprm_id, m.*');
    }

    public function getLastInPPTournament(string $type, int $typeId){
        $this->db->join('ppRoundMatches pprm','pprm.ppRound_id=ppRounds.id','INNER');
        $this->db->join('matches m','pprm.match_id=m.id','INNER');
        $this->db->where('ppRounds.'.$type, $typeId);
        $this->db->where('m.verified_at IS NOT NULL');
        $this->db->orderBy('m.date_start','desc');
        return $this->db->getOne('ppRounds','pprm.id as pprm_id, m.*');
    }

    public function hasLiveMatch(array $ids){
        $this->db->where('id', $ids, 'IN');
        $this->db->where('verified_at IS NULL');

        $start = date("Y-m-d H:i:s", strtotime('-120 minutes'));
        $end = date("Y-m-d H:i:s");
        $this->db->where('date_start', array($start, $end), 'BETWEEN');

        // $start = date("Y-m-d H:i:s", strtotime('-3 days'));
        // $finish = date("Y-m-d H:i:s", strtotime('+100 minutes'));
        // $this->db->where('m.date_start', array($start, $finish), 'BETWEEN');


        return $this->db->has('matches');
    }

    public function create(int $ls_id, int $league_id, ?int $home_id, ?int $away_id, int $round, string $date_start){
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
	    );

        $this->db->where('id', $id);
        $this->db->update('matches', $data, 1);
    }

    public function updateNotes(int $id, string $notes){
        $data = array(
			"notes" => $notes,
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
    
    public function updateExternalId(int $id, int $newLs_id){
        $data = array(
			"ls_id" => $newLs_id,
	    );
        $this->db->where('id', $id);
        return $this->db->update('matches', $data, 1);
    }

    public function verify(int $id, int $score_home, int $score_away, ?string $notes=null){
        $data = array(
			"score_home" => $score_home,
			"score_away" => $score_away,
            "notes" => $notes,
            "verified_at" => $this->db->now()
	    );
        $this->db->where('id', $id);
        $this->db->update('matches', $data, 1);
    }

    private function getNextRoundNumber(int $league_id) : ?int{
        $lastRound = $this->getLastRoundNumber($league_id);
        if($lastRound)$this->db->where('round', $lastRound, '>');

        $this->db->where('league_id', $league_id);
        //to exclude round-of-16 and such when groups not finished.
        $this->db->where('home_id IS NOT NULL');
        $this->db->where('away_id IS NOT NULL');

        $this->db->where('date_start', date('Y-m-d H:i:s'), '>');
        $this->db->orderBy('date_start', 'asc');

        return $this->db->getValue('matches','round');
    }
    
    private function getLastRoundNumber(int $league_id) : ?int{
        $this->db->where('league_id', $league_id);
        $this->db->where('date_start', date('Y-m-d H:i:s'), '<');
        $this->db->orderBy('date_start', 'desc');
        return $this->db->getValue('matches', 'round');
    }

    public function getNextRoundForLeague(int $league_id, ?int $limit=null) : ?array{
        if(!$nextRoundNumber = $this->getNextRoundNumber($league_id)){
            return [];
        }
        
        $this->db->where('round', $nextRoundNumber);
        $this->db->where('league_id', $league_id);

        $minTimeInterval = date("Y-m-d H:i:s", strtotime('+1 days'));
        $this->db->where('date_start', $minTimeInterval, '>');

        $this->db->orderBy('date_start', 'asc');
        
        return $this->db->get('matches', $limit);
    }

    public function isBeforeStartTime(int $id):bool{
        $this->db->where('id', $id);
        $now = date("Y-m-d H:i:s");
        $this->db->where('date_start', $now, '>');
        return $this->db->has('matches');
    }

    public function getMatchesForLeagues(
        array $league_ids, 
        ?int $from_days_diff = null, 
        ?int $until_days_diff = null, 
        ?string $sort = 'ASC', 
        ?int $limit = 50,
        ?bool $verified = null
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
        if($verified !== null)$this->db->where('verified_at IS '.($verified ? ' NOT ' : '').' NULL');    

        $this->db->where('league_id', $league_ids, 'IN');
        $this->db->orderBy('date_start', $sort);
        
        return $this->db->get('matches', $limit);
    }

    public function delete(int $id){
        $this->db->where('matches.id', $id);
        $this->db->where('matches.verified_at IS NULL');
        return $this->db->delete('matches',1);
    }

    public function pickForToday(){
        $this->db->where('date(date_start) = CURDATE()');
        $this->db->where('time(date_start) > "11.30"');
        $this->db->where("verified_at IS NULL");
        $this->db->orderBy("rand()");
        return $this->db->getOne('matches', $this->whitelistColumns);
    }

}
