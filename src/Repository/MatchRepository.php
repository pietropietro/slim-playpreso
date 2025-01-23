<?php

declare(strict_types=1);

namespace App\Repository;

final class MatchRepository extends BaseRepository
{   
    private $whitelistColumns = array('id','league_id','home_id','away_id','score_home','score_away','round','date_start','verified_at');
    private $minTimeInterval = '+1 days';

    public function adminGet(
        ?array $ids = null,
        ?string $country = null,
        ?int $leagueId = null,
        ?string $from = null,
        ?string $to = null,
        ?int $level = null
    ): array {
    
        if ($leagueId) {
            $subLeagueIds = $this->db->rawQuery("SELECT id FROM leagues WHERE parent_id = ? OR id = ?", [$leagueId, $leagueId]);
            $leagueIds = array_column($subLeagueIds, 'id');
            $this->db->where('m.league_id', $leagueIds, 'IN');
        }

        if ($country || $level) {
            $this->db->join('leagues l', 'm.league_id = l.id', 'INNER');
            if($country) $this->db->where('l.country', $country);
            if($level) $this->db->where('l.level', $level);
        }
            
        if($from)$this->db->where('m.date_start', $from, '>=');
        if($to)$this->db->where('m.date_start', $to, '<=');
    
        // Add condition for ids if provided
        if ($ids) {
            $this->db->where('m.id', $ids, 'IN');
        }
    
        $this->db->join('ppRoundMatches pprm', "pprm.match_id=m.id", "left");
        $this->db->groupBy('m.id');
        $this->db->orderBy('date_start', 'ASC');
    
        $columns = [
            'm.id', 'm.ls_id', 'm.league_id', 
            'm.home_id', 'm.away_id', 'm.score_home', 'm.score_away', 
            'm.round', 'm.date_start', 'm.created_at', 'm.verified_at', 'm.notes', 
            'count(distinct pprm.motd) as motd', 'count(distinct pprm.flash) as flash'
        ];
    
        $matches = $this->db->get('matches m', null, $columns);
        return $matches;
    }
    

    // TODO admin if needed can get this extra data
    // 'count(distinct g.ppRoundMatch_id) as ppRMcount', 
    // 'count(distinct pprm.id) as aggregatePPRM',
    // 'count(distinct pprm.motd) as motd',
    // 'count(g.id) as aggregateGuesses',
    // 'ROUND(sum(g.UNOX2)/count(guessed_at) * 100) as aggregateUNOX2',
    // 'ROUND(sum(g.GGNG)/count(guessed_at) * 100) as aggregateGGNG',
    // 'ROUND(sum(g.UO25)/count(guessed_at) * 100) as aggregateUO25',
    // 'ROUND(sum(g.PRESO)/count(guessed_at) * 100) as aggregatePRESO',


    public function getCountByMonth(int $year, int $month): array {
        // Ensure month is zero-padded
        $month = str_pad((string)$month, 2, '0', STR_PAD_LEFT);

        // Calculate the first and last day of the month
        $firstDayOfMonth = "$year-$month-01";
        $lastDayOfMonth = date('Y-m-t 23:59:59', strtotime($firstDayOfMonth)); // Include full last day

        // Construct the query
        $this->db->where('m.date_start', $firstDayOfMonth, '>=');
        $this->db->where('m.date_start', $lastDayOfMonth, '<=');

        // Self-join to get parent league names if necessary
        $this->db->join("leagues l", "m.league_id = l.id", "LEFT");
        $this->db->join("leagues lp", "l.parent_id = lp.id AND l.parent_id != l.id", "LEFT");
    
        $this->db->groupBy("DATE(m.date_start)");
        $this->db->orderBy("DATE(m.date_start)", "asc");
    
        $fields = [
            "DATE(m.date_start) AS match_day",
            "COUNT(*) AS match_count",
            "JSON_ARRAYAGG(
                JSON_OBJECT(
                    'country', l.country, 
                    'league', l.name, 
                    'parent_id', l.parent_id, 
                    'league_id', l.id, 
                    'parent_name', IFNULL(lp.name, l.name),
                    'level', l.level
                )
            ) AS matches_from",
            "(CASE WHEN COUNT(m.verified_at) = COUNT(*) THEN 1 ELSE 0 END) AS all_matches_verified"
        ];
    
        $matchSummary = $this->db->get("matches m", null, $fields);
        return $matchSummary;
    }
    
    
    


    public function get(array $ids){
        if(!$ids)return;
        $this->db->where('id', $ids, 'IN');
        return $this->db->get('matches');
    }

    public function getOne(int $matchId, bool $is_external_id = false, bool $admin = false) : ?array {
        $column = !!$is_external_id ? 'ls_id' : 'id';
        $this->db->where($column, $matchId);

        if($admin){
            return $this->db->getOne('matches');
        }
        
        return $this->db->getOne('matches', $this->whitelistColumns);
    }

    public function getOneByLeagueRoundAndTeams(
        int $leagueId, 
        int $round, 
        int $homeId, 
        int $awayId,
        ?string $date_start_condition = ' > now()'
    ){
        $this->db->where('league_id', $leagueId);
        $this->db->where('round', $round);
        $this->db->where('home_id', $homeId);
        $this->db->where('away_id', $awayId);
        $this->db->where('verified_at IS NULL');
        $this->db->where('date_start '.$date_start_condition);
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

    public function updateNotes(int $id, ?string $notes=null){
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

    public function updateLeague(int $id, int $league_id){
        $data = array(
			"league_id" => $league_id,
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
        $maxInterval = date("Y-m-d H:i:s", strtotime('-30 days'));
        $this->db->where('date_start', $maxInterval, '>');

        $this->db->orderBy('date_start', 'desc');
        //get last 5 matches since league could have ended with some 'recuper' of earlier rounds
        $rounds = $this->db->getValue('matches', 'round', 5);
        if(!$rounds)return null;
        return max($rounds);
    }

    public function getNextRoundForLeague(int $league_id, ?int $limit=null) : ?array{
        if(!$nextRoundNumber = $this->getNextRoundNumber($league_id)){
            return [];
        }
        
        $this->db->where('round', $nextRoundNumber);
        $this->db->where('league_id', $league_id);
        $this->db->join('leagues l', 'l.id = matches.league_id', 'INNER');

        $dateInterval = date("Y-m-d H:i:s", strtotime($this->minTimeInterval));
        $this->db->where('date_start', $dateInterval, '>');
        $this->db->where('verified_at is null');
        $this->db->orderBy('date_start', 'asc');

        $this->db->join("teams as home_team", "matches.home_id = home_team.id", "INNER");
        $this->db->join("teams as away_team", "matches.away_id = away_team.id", "INNER");
        $this->db->where("(home_team.name NOT LIKE '%group%' 
            AND away_team.name NOT LIKE '%group%' 
            AND home_team.name NOT LIKE '%winner%' 
            AND away_team.name NOT LIKE '%winner%' 
            AND home_team.name NOT LIKE '%/%' 
            AND away_team.name NOT LIKE '%/%')");

        
        return $this->db->get('matches', $limit, 'matches.*, COALESCE(l.parent_id, l.id) as league_parent_id, l.level as league_level');
    }

    public function isBeforeStartTime(int $id):bool{
        $this->db->where('id', $id);

        $minutesAllowed = $_SERVER['ALLOW_LOCK_MINUTES_BEFORE_START'] ?? 10;
        $offsetTime = '+' . $minutesAllowed . ' minutes';
        $adjustedNow = date("Y-m-d H:i:s", strtotime($offsetTime));

        $this->db->where('date_start', $adjustedNow, '>');
    
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
        if(isset($verified))$this->db->where('verified_at IS '.($verified ? ' NOT ' : '').' NULL');    

        $this->db->where('league_id', $league_ids, 'IN');
        $this->db->orderBy('date_start', $sort);
        
        return $this->db->get('matches', $limit);
    }

    public function delete(int $id){
        $this->db->where('matches.id', $id);
        $this->db->where('matches.verified_at IS NULL');
        return $this->db->delete('matches',1);
    }

    public function adminPickForToday(?int $limit = 1)
    {
        // Filter by today's date and time after 11:30
        $this->db->where('date(date_start) = CURDATE()');
        $this->db->where('time(date_start) > "11:30:00"');
        $this->db->where("verified_at IS NULL");
    
        // Join leagues and countryWeights tables
        $this->db->join('leagues l', 'l.id=matches.league_id', 'inner');
        $this->db->join('countryWeights cw', 'cw.country=l.country', 'inner');
    
        // // Order first by the sum of l.level + cw.weight
        $this->db->orderBy("l.level + cw.weight + l.weight_offset", 'ASC');

        // Then order by random if multiple rows have the same sum
        $this->db->orderBy("RAND()");
    
        // Get the result based on the limit
        if ($limit == 1) {
            return $this->db->getOne('matches', 'matches.*');
        }
        return $this->db->get('matches', $limit, 'matches.*');
    }
    
    public function nextMatches(int $league_id, int $limit = 20){
        $this->db->where('league_id', $league_id);

        $dateInterval = date("Y-m-d H:i:s", strtotime($this->minTimeInterval));
        $this->db->where('date_start', $dateInterval, '>');
        $this->db->where('verified_at is null');
        
        $this->db->join('leagues l', 'l.id = matches.league_id', 'INNER');
        $this->db->join("teams as home_team", "matches.home_id = home_team.id", "INNER");
        $this->db->join("teams as away_team", "matches.away_id = away_team.id", "INNER");
        $this->db->where(
            "(home_team.name NOT LIKE '%group%' 
                AND away_team.name NOT LIKE '%group%' 
                AND home_team.name NOT LIKE '%winner%' 
                AND away_team.name NOT LIKE '%winner%' 
                AND home_team.name NOT LIKE '%/%' 
                AND away_team.name NOT LIKE '%/%')
            "
        );

        $this->db->orderBy('date_start', 'asc');
        
        return $this->db->get('matches', $limit, 'matches.*, COALESCE(l.parent_id, l.id) as league_parent_id, l.level as league_level');
    }

    public function getLastForTeam(int $id, int $limit = 5){
        $this->db->where("(home_id={$id} or away_id={$id}) and verified_at is not null");
        $this->db->orderBy('date_start');
        return $this->db->get('matches', $limit);
    }

}
