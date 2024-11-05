<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Guess;

final class GuessRepository extends BaseRepository
{
    public function getOne(int $id){
        $this->db->where('id', $id);
        return $this->db->getOne('guesses');
    }

    public function get(array $ids){
        $this->db->where('id', $ids, 'IN');
        return $this->db->get('guesses');
    }

    public function getForUser(
        int $userId, 
        ?bool $includeMotd = true, 
        ?bool $locked = null, 
        ?bool $verified = null,
        ?string $verified_after = null,
        ?string $order = 'asc',
        ?int $offset = null, 
        ?int $limit = 200
    ) {
        $this->db->join('matches m', 'm.id=guesses.match_id', 'INNER');
    
        if (!$includeMotd) {
            $this->db->join('ppRoundMatches pprm', 'pprm.id=guesses.ppRoundMatch_id', 'INNER');
            $this->db->where('pprm.motd IS NULL');
        }
    
        if (isset($locked)) {
            $this->db->where('guesses.verified_at IS NULL');
            $this->db->where('guessed_at ' . ($locked ? 'IS NOT' : 'IS') . ' NULL');
        }
    
        if (isset($verified)) {
            $this->db->where('m.verified_at ' . ($verified ? 'IS NOT' : 'IS') . ' NULL');
            if(isset($verified_after)){
                $this->db->where('guesses.verified_at', $verified_after, '>=');
            }
        }
    
        $this->db->where('user_id', $userId);
        $this->db->orderBy('m.date_start', $order);
    
        return $this->db->get('guesses',[$offset, $limit], 'guesses.*');
    }
    

    public function getLast(int $userId, ?string $afterString = null, ?int $limit = null) : array {
        $this->db->where('user_id', $userId);
        $this->db->where('guesses.verified_at IS NOT NULL');
        $this->db->orderBy('guesses.verified_at');

        //i.e. "-3 months"
        if($afterString){
            $this->db->where('verified_at', date("Y-m-d H:i:s", strtotime($afterString)), ">");
        }
        return $this->db->get('guesses', $limit);
    }

    public function getForPPRoundMatch(int $ppRoundMatchId, ?int $userId=null){
        $this->db->where('ppRoundMatch_id', $ppRoundMatchId);
        if($userId){
            $this->db->where('user_id', $userId);
            return $this->db->getOne('guesses');
        }
        $this->db->join("users u", "u.id=g.user_id", "INNER");
        $this->db->orderBy('g.points','desc');
        $this->db->orderBy('g.guessed_at','desc');
        return $this->db->get('guesses g', null, array('g.*, u.username'));
    }

    public function countForPPRoundMatch(int $ppRoundMatchId){
        $this->db->where('ppRoundMatch_id', $ppRoundMatchId);
        return $this->db->getValue('guesses', 'count(id)');
    }

    public function getForMatch(int $matchId, bool $not_verified){
        $this->db->where('match_id', $matchId);
        if($not_verified){
            $this->db->where('verified_at IS NULL');
        }
        return $this->db->get('guesses');
    }

    public function getForTeam(int $teamId, int $userId, ?string $from=null, ?string $to=null){
        $this->db->join("matches m", "m.id=guesses.match_id", "INNER");

        $this->db->where('user_id', $userId);

        if($from) $this->db->where('m.verified_at', $from, ">=");
        if($to) $this->db->where('m.verified_at',$to, "<=");

        $teamIdCondition = "(home_id = " . $this->db->escape($teamId) . " OR away_id = " . $this->db->escape($teamId) . ")";
        $this->db->where($teamIdCondition);
        $this->db->orderBy('verified_at', 'desc');
        return $this->db->get('guesses', null, 'guesses.*');
    }

    public function getForLeague(int $leagueId, int $userId, ?string $from=null, ?string $to=null){
        $this->db->where('user_id', $userId);
        $this->db->join("matches m", "m.id=guesses.match_id", "INNER");
        $this->db->join("leagues l", "m.league_id = l.id", "INNER");

        if($from) $this->db->where('m.verified_at', $from, ">=");
        if($to) $this->db->where('m.verified_at', $to, "<=");

        $leagueIdCondition = "(m.league_id = " . $this->db->escape($leagueId) . " OR l.parent_id = " . $this->db->escape($leagueId) . ")";
        $this->db->where($leagueIdCondition);

        $this->db->orderBy('verified_at', 'desc');
        return $this->db->get('guesses', null, 'guesses.*');
    }

    public function lock(int $id, int $home, int $away){
        $data = array(
            "home" => $home,
            "away" => $away,
            "guessed_at" => $this->db->now()
        );
        $this->db->where('id', $id);
        $this->db->update('guesses', $data, 1);  
    }

    public function verify(int $id, ?bool $unox2, ?bool $uo25, ?bool $ggng, ?bool $preso, ?int $points){
        $data = array(
            "UNOX2" => $unox2,
            "GGNG" => $ggng,
            "UO25" => $uo25,
            "PRESO" => $preso,
            "points" => $points,
            "verified_at" => $this->db->now()
        );

        $this->db->where('id', $id);
        $this->db->update('guesses', $data, 1);        
    }

    public function create($userId, $matchId, $ppRoundMatchId) : int {
        $data = array(
            "user_id" => $userId,
            "match_id" => $matchId,
            "ppRoundMatch_id" => $ppRoundMatchId,
            "created_at" => $this->db->now()
        );
        return $this->db->insert('guesses', $data);
    }

    public function createdebug($userId, $matchId, $ppRoundMatchId) {
        //MISS SOME
        $missed = rand(0,6) === 6;
        $data = array(
            "user_id" => $userId,
            "match_id" => $matchId,
            "ppRoundMatch_id" => $ppRoundMatchId,
            "guessed_at" => $missed ? null : $this->db->now(),
            "home" => $missed ? null : rand(0,3),
            "away" => $missed ? null : rand(0,3),
            "created_at" => $this->db->now()
        );
        return $this->db->insert('guesses', $data);
    }

    //TODO CHANGE COLUMN TO ENUM ['league_id', 'cup_group_id']
    public function countUpNumbers(int $userId, string $tournamentColumn, int $tournamentId) {
        $ids = $this->db->subQuery();
        $ids->where($tournamentColumn, $tournamentId);
        $ids->get('ppRounds', null, 'id');

        $this->db->where('ppRound_id',$ids,'IN');
        
        if($ppRMIds = $this->db->getValue('ppRoundMatches','id',null)){
            $this->db->where('user_id',$userId);
            $this->db->where('ppRoundMatch_id', $ppRMIds,'in');
            $this->db->where("verified_at IS NOT NULL");
            $this->db->where("guessed_at IS NOT NULL");
            
            $columns = array(
                'sum(points) as tot_points', 
                'sum(preso) as tot_preso', 
                'sum(UNOX2) as tot_unox2',
                'sum(UO25) as tot_uo25',
                'sum(GGNG) as tot_ggng', 
                'count(id) as tot_locked'
            );

            if($upResult = $this->db->getOne('guesses', $columns)){
                return $upResult;
            }
        }
    }

    public function countScoreDifference(string $tournamentColumn, int $tournamentId, int $userId){
        if(!in_array($tournamentColumn, array('ppLeague_id', 'ppCupGroup_id')) ) return;

        $sql = "
                SELECT (ABS(SUM(realHome) - SUM(home)) + ABS(SUM(realAway) - SUM(away))) AS tot_score_diff 
                FROM (
                    SELECT user_id, home, away, 
                        IF(matches.score_home <= 3, matches.score_home, 3) AS realHome, 
                        IF(matches.score_away <= 3, matches.score_away, 3) AS realAway, 
                        matches.verified_at 
                    FROM guesses  
                    INNER JOIN matches ON matches.id=guesses.match_id 
                    INNER JOIN ppRoundMatches pprm ON pprm.id=guesses.ppRoundMatch_id 
                    INNER JOIN ppRounds ppr ON ppr.id=pprm.ppRound_id
                    WHERE guesses.user_id = ?
                    AND matches.verified_at IS NOT NULL 
                    AND ppr.".$tournamentColumn." = ?
                ) AS q1 
                INNER JOIN users ON q1.user_id = users.id
                GROUP BY user_id;
            ";

            // Execute the query
            $result = $this->db->rawQuery($sql, array($userId, $tournamentId));
            if($result) return $result[0];
    }

    public function changePPRMMatch(int $ppRoundMatch_id, int $newMatchId){
        $data = array(
            "guessed_at" => null,
            "home" => null,
            "away" => null,
            "match_id" => $newMatchId
        );
        $this->db->where('ppRoundMatch_id', $ppRoundMatch_id);
        $this->db->update('guesses', $data);     
    }

    public function deletePPRMMatch(int $ppRoundMatch_id){
        if(!$ppRoundMatch_id)return;
        $this->db->where('ppRoundMatch_id', $ppRoundMatch_id);
        $this->db->where('verified_at is null');
        return $this->db->delete('guesses');
    }

    //TODO MOVE TO SERVICE
    //TODO CHANGE COLUMN TO ENUM ['cup_id', 'league_id',]
    //possible duplicate
    public function hasUnlockedGuesses(int $userId, string $column, int $valueId){
        $ppRoundIds = $this->db->subQuery();
        $ppRoundIds->where($column, $valueId);
        $ppRoundIds->get('ppRounds',null,'id');
        
        $ppRoundMatchIds = $this->db->subQuery();
        $ppRoundMatchIds->where('ppRound_id', $ppRoundIds, 'IN');
        $ppRoundMatchIds->get('ppRoundMatches',null,'id');

        $this->db->where('ppRoundMatch_id', $ppRoundMatchIds, 'IN');
        $this->db->where('guessed_at IS NULL');
        $this->db->where('verified_at IS NULL');
        
        $result = $this->db->getOne('guesses');
        return !!$result;

    }

    public function lastLock(int $userId){
        $this->db->where('user_id', $userId);
        $this->db->orderBy('guessed_at');
        return $this->db->getValue('guesses','guessed_at');
    }

    public function verifyMissed(){
        $data = array(
            "g.verified_at" => $this->db->now()
        );

        $minutesAllowed = $_SERVER['ALLOW_LOCK_MINUTES_BEFORE_START'] ?? 10;
        $before = date("Y-m-d H:i:s", strtotime('+' . $minutesAllowed . ' minutes'));

        $this->db->join("matches m", "m.id=g.match_id", "INNER");
        $this->db->where('m.date_start', $before, '<');
        $this->db->where('g.guessed_at IS NULL');
        $this->db->where('g.verified_at IS NULL');

        $this->db->update('guesses g', $data);      
    }

    public function bestUsersInRound(array $ppRMids, int $limit=3){
        $this->db->join("users u", "u.id=guesses.user_id", "INNER");
        $this->db->groupBy('guesses.user_id');
        $this->db->orderBy('sum_points','desc');
        $this->db->where('ppRoundMatch_id', $ppRMids, 'IN');
        $columns = ['sum(guesses.points) as sum_points', 'u.username'];
        return $this->db->get('guesses', $limit, $columns);
    }


    public function getLastPreso(int $limit = 1){
        $this->db->where('PRESO',1);
        $this->db->orderBy('verified_at','desc');
        return $this->db->get('guesses', $limit);
    }


    public function getUnlockedGuessesStarting(?string $interval = '+6 hours', ?bool $withoutUserNotification = true ){
        $this->db->where('guesses.guessed_at IS NULL');
        $this->db->where('guesses.verified_at IS NULL');
        

        $this->db->join('matches m', "m.id=guesses.match_id", "INNER");
        $dateInterval = date("Y-m-d H:i:s", strtotime($interval));
        $this->db->where('m.date_start', $dateInterval, '<');
        $this->db->where('m.date_start > now()');

        if($withoutUserNotification){
            $this->db->join('userNotifications un', 'un.event_id = guesses.id AND un.event_type = "guess_unlocked_starting"', 'LEFT');
            $this->db->where('un.id IS NULL');  // This ensures we get rows where there is no match in userNotifications            
        }

        $result = $this->db->get('guesses',null, ['guesses.id, guesses.user_id', 'm.date_start']);
        return $result;
    }

}
