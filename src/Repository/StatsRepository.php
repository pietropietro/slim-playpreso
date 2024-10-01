<?php

declare(strict_types=1);

namespace App\Repository;

final class StatsRepository extends BaseRepository
{   

    public function bestAverage(?int $userId=null){

        if($userId){
            $this->db->where('g.user_id', $userId);
        }
        $limit = 5;
        $from = date("Y-m-d H:i:s", strtotime('- 1 month'));
        $this->db->where('verified_at', $from, '>');
        $this->db->join('users u', 'g.user_id=u.id', 'INNER');
        $this->db->groupBy('g.user_id');
        $this->db->orderBy('display_points');
        $this->db->having('cnt', 14, '>');
        $columns = array('g.user_id', 
            'u.username', 
            'ROUND(avg(coalesce(g.points,0)),1) as display_points',
            'count(*) as cnt',
            'count(guessed_at) as cnt_locked',
            'sum(PRESO) as cnt_preso',
            'sum(UNOX2) as cnt_1x2', 
            'sum(UO25) as cnt_uo25', 
            'sum(GGNG) as cnt_ggng'
        );
        return $this->db->get('guesses g', $limit, $columns);
    }

    public function mostPoints(?int $userId=null){
        if($userId){
            $this->db->where('g.user_id', $userId);
        }
        $limit = 5;
        $from = date("Y-m-d H:i:s", strtotime('- 1 month'));
        $this->db->where('verified_at', $from, '>');
        $this->db->join('users u', 'g.user_id=u.id', 'INNER');
        $this->db->groupBy('g.user_id');
        $this->db->orderBy('display_points');
        $columns = array('g.user_id', 
            'u.username', 
            'SUM(g.points) as display_points',
            'count(*) as cnt',
            'count(guessed_at) as cnt_locked'
        );
        return $this->db->get('guesses g', $limit, $columns);
    }

    public function countCommonScore(){
        $this->db->where('verified_at IS NOT NULL');
        $sql = 'SELECT COUNT("score_home") as occurrances, score_home,
            ROUND((COUNT("score_home") / (SELECT COUNT("id") FROM matches WHERE verified_at IS NOT NULL)) * 100,1) as percent
            FROM matches';


        $this->db->groupBy('score_home');
        $this->db->orderBy('occurrances');
        return $this->db->query($sql);
    }

    public function countCommonResults(){
        $this->db->where('verified_at IS NOT NULL');
        $sql = 'SELECT CONCAT(score_home, "-", score_away) as concat_result, COUNT("concat_result") as occurances, 
            (COUNT("concat_result") / 12670)  * 100 AS total FROM matches';

        //(SELECT COUNT("id") FROM matches WHERE verified_at IS NOT NULL)
        //12670

        $this->db->groupBy('concat_result');
        $this->db->orderBy('occurances');
        return $this->db->query($sql);
    }
    

    // Array
    // (
    //     [result] => 1-0
    //     [occurrances] => 134
    // )
    public function getCommonLock(
        ?int $ppRoundMatchId =null,
        ?int $userId = null,
        ?string $from = null, 
        ?string $to=null
    ){
        if($ppRoundMatchId)$this->db->where('ppRoundMatch_id', $ppRoundMatchId);
        if($userId)$this->db->where('user_id', $userId);
        if($from)$this->db->where('guessed_at', $from, '>=');
        if($to)$this->db->where('guessed_at', $to, '<=');
        $this->db->where('guessed_at IS NOT NULL');
        $this->db->orderBy('most_lock_combination_tot');
        $this->db->groupBy('most_lock_combination');
        return $this->db->get('guesses', 3,
            'concat_ws("-",home,away) as most_lock_combination, count(*) as most_lock_combination_tot'
        );
    }

    public function getPPRMAggregates(int $ppRoundMatchId){
        $this->db->where('ppRoundMatch_id', $ppRoundMatchId);
        $this->db->where('guessed_at is not null');
        $this->db->groupBy('ppRoundMatch_id');
        $columns = 'ROUND(avg(points),1) as points_avg, sum(PRESO) as preso_count';
        return $this->db->getOne('guesses', $columns);
    }

    //  COUNT(g.PRESO) as cnt_preso
    //This query will count the occurrences of each team as 
    //either home or away in the guesses of the specified user and then return the top 3 teams
    public function getUserCommonTeams(int $userId, ?string $from = null, ?string $to = null){
        // Set default broad date ranges if $from or $to is not provided
        $from = $from ?? '1900-01-01 00:00:00';
        $to = $to ?? '2100-12-31 23:59:59';
    
        $sql = "
            SELECT t.id, t.name, COUNT(*) as tot_locks, round(AVG(COALESCE(subquery.points, 0)),1) as avg_points, SUM(subquery.preso) as tot_preso
            FROM (
                SELECT home_id as team_id, g.points, g.preso FROM guesses g
                JOIN matches m ON g.match_id = m.id
                WHERE g.user_id = ? 
                AND g.verified_at BETWEEN ? AND ?
                UNION ALL
                SELECT away_id as team_id, g.points, g.preso FROM guesses g
                JOIN matches m ON g.match_id = m.id
                WHERE g.user_id = ? 
                AND g.verified_at BETWEEN ? AND ?
            ) as subquery
            JOIN teams t ON subquery.team_id = t.id
            GROUP BY t.id, t.name
            ORDER BY tot_locks DESC
            LIMIT 3
        ";
    
        $params = [$userId, $from, $to, $userId, $from, $to]; 
        return $this->db->rawQuery($sql, $params);
    }
    

    // [0] => Array
    // (
    //     [id] => 216
    //     [name] => AZ Alkmaar
    //     [avg_points] => 9.8
    //     [occurrences] => 5
    // )
    //true best, false worst
    public function getUserExtremeAverageTeams(int $userId, ?string $from = null, ?string $to = null, bool $bestWorstFlag = true){
        // Default values for $from and $to if not provided
        $from = $from ?? '1900-01-01 00:00:00';
        $to = $to ?? '2100-12-31 23:59:59';
    
        $sql = "
            SELECT t.id, t.name, t.country, round(AVG(COALESCE(subquery.points, 0)),1) as avg_points, COUNT(*) as tot_locks
            FROM (
                SELECT home_id as team_id, g.points FROM guesses g
                JOIN matches m ON g.match_id = m.id
                WHERE g.user_id = ? 
                AND g.verified_at BETWEEN ? AND ?
                UNION ALL
                SELECT away_id as team_id, g.points FROM guesses g
                JOIN matches m ON g.match_id = m.id
                WHERE g.user_id = ? 
                AND g.verified_at BETWEEN ? AND ?
            ) as subquery
            JOIN teams t ON subquery.team_id = t.id
            GROUP BY t.id, t.name
            HAVING COUNT(*) >= ?
            ORDER BY avg_points ".($bestWorstFlag ? "DESC" : "ASC")."
            LIMIT 3
        ";
    
        $params = [$userId, $from, $to, $userId, $from, $to, 5];
        return $this->db->rawQuery($sql, $params);
    }
    


    //     Array
    // (
        // [0] => Array
        // (
        //     [locks] => 705
        //     [avg_points] => 5.0
        //     [perc_unox2] => 40.9
        //     [perc_ggng] => 46.4
        //     [perc_uo25] => 50.4
        //     [count_preso] => 71
        // )

    //stats only for locked guesses
    public function getUserMainSummary(int $userId, ?string $from = null, ?string $to=null){
        $this->db->where('user_id', $userId);
        if($from)$this->db->where('verified_at', $from, '>=');
        if($to)$this->db->where('verified_at', $to, '<=');
        $this->db->groupBy('user_id'); // Group by user_id if you want to aggregate over all records of the user
        $columns = [
            'COUNT(*) AS tot_locks',
            'round(AVG(COALESCE(points, 0)),1) as avg_points',
            'round(100 * SUM(UNOX2 = 1) / COUNT(*),1) AS perc_unox2',
            'round(100 * SUM(GGNG = 1) / COUNT(*),1) AS perc_ggng',
            'round(100 * SUM(UO25 = 1) / COUNT(*),1) AS perc_uo25',
            'SUM(PRESO = 1) AS tot_preso',
            'SUM(points) AS tot_points',
        ];
        return $this->db->getOne('guesses', $columns);
    }


    public function getUserMissedCount(int $userId, ?string $from = null, ?string $to=null){
        $this->db->where('user_id', $userId);
        if($from)$this->db->where('verified_at', $from, '>=');
        if($to)$this->db->where('verified_at', $to, '<=');
        $this->db->where('verified_at IS NOT NULL');
        $this->db->groupBy('user_id');
        $column = 'SUM(guessed_at IS NULL) AS tot_missed';
        $res = $this->db->getOne('guesses', $column);
        return $res;
    }

    // (
    // [0] => Array
    // (
    //     [id] => 2
    //     [league_name] => Premier League
    //     [total_guesses] => 63
    //     [avg_points] => 4.0952
    // )
    //commonBestFlag true returns common, false returns best avg
    
    public function getUserLeagues(int $userId, ?string $from = null, ?string $to=null, int $commonHighLow){
        $this->db->join("matches m", "g.match_id = m.id", "INNER");
        $this->db->join("leagues l", "m.league_id = l.id", "INNER");
        $this->db->join("leagues pl", "l.parent_id = pl.id", "LEFT");
    
        $this->db->where("g.user_id", $userId);
        if($from) $this->db->where('g.verified_at', $from, '>=');
        if($to) $this->db->where('g.verified_at', $to, '<=');
    
        $this->db->groupBy("COALESCE(l.parent_id, l.id)");
    
        if($commonHighLow === 0) {
            $this->db->orderBy("COUNT(*)", "DESC");
        } else {
            $this->db->having('COUNT(*)', 5, '>=');
            $this->db->orderBy("AVG(COALESCE(g.points, 0))", $commonHighLow === 1 ? "DESC" : 'ASC');
        }
    
        $columns = [
            "COALESCE(MIN(l.parent_id), MIN(l.id)) as id",
            "COALESCE(MIN(pl.name), MIN(l.name)) as name",
            "MIN(l.parent_id) as parent_id",
            "MIN(l.country) as country",
            "COUNT(*) as tot_locks",
            //TODO check if missed should have 0 points instead of null
            // Coalesce so null points are counted as 0
            "round(AVG(COALESCE(g.points, 0)), 1) as avg_points"
        ];
        
    
        $result = $this->db->get("guesses g", 3 ,$columns);
    
        return $result;
    }    



    function getExtremeMonth(int $userId, int $year, bool $isBest = true) {
    
        // Choose the order based on whether we want the best or worst month
        $orderByDirection = $isBest ? 'DESC' : 'ASC';
    
        $this->db->where('user_id', $userId);
        $this->db->where('guessed_at IS NOT NULL');
        $this->db->where("YEAR(guessed_at)", $year, '=');
        $this->db->groupBy('YEAR(guessed_at), MONTH(guessed_at)');
        $this->db->having('COUNT(id)', 10, '>=');
        $this->db->orderBy('AVG(points)', $orderByDirection);
    
        $columns = ['YEAR(guessed_at) AS year, 
                    MONTH(guessed_at) AS month, 
                    round(AVG(COALESCE(points, 0)),1) as avg_points',
                    'count(id) as tot_locks',
                    'sum(preso) as tot_preso'
                ];

        return $this->db->getOne('guesses', $columns);
    }

    function countPPLeagueParticipations(int $userId, int $year){
        // Count the total number of participations in leagues
        $this->db->where("YEAR(updated_at)", $year, '=');
        $this->db->where('user_id', $userId);
        $this->db->where('ppLeague_id IS NOT NULL');
        $totalParticipations = $this->db->getValue('userParticipations', 'count(id)');

        return  $totalParticipations;
        
    }

    public function mostPPLeagueParticipations(int $userId, int $year){
        // Find the most joined ppTournamentType_id
        $this->db->where("YEAR(updated_at)", $year, '=');
        $this->db->where('user_id', $userId);
        $this->db->where('ppLeague_id IS NOT NULL');
        $this->db->join('ppTournamentTypes ppts', 'userParticipations.ppTournamentType_id=ppts.id');
        $this->db->groupBy('name');
        $this->db->orderBy('count(name)', 'DESC');
        $mostJoinedTournamentType = $this->db->getOne('userParticipations', 
            'name as ppl_most_kind_name, 
                count(name) as ppl_most_kind_tot, 
                group_concat(userParticipations.id) as ups_ids
            '
        );
        return $mostJoinedTournamentType;
    }

    //FIND USER that has more ups with specific user
    function getUsersWithMostParticipationsWith(int $userId, int $year) {
        // First, get the league and cup group IDs where the user participated
        $this->db->where('user_id', $userId);
        $this->db->where("YEAR(updated_at)", $year, '=');
        $userLeaguesAndCups = $this->db->get('userParticipations', null, 'ppLeague_id, ppCupGroup_id');

        // Extract league and cup group IDs
        $ppLeagueIds = array_column($userLeaguesAndCups, 'ppLeague_id');
        $ppCupGroupIds = array_column($userLeaguesAndCups, 'ppCupGroup_id');

        // Now, count participations for other users in these leagues and cup groups
        $this->db->where('user_id', $userId, '!=');
       
        if (!empty($ppLeagueIds)) {
            $this->db->where('ppLeague_id', $ppLeagueIds, 'IN');
            if (!empty($ppCupGroupIds)) {
                $this->db->orWhere('ppCupGroup_id', $ppCupGroupIds, 'IN');
            }
        }

        $this->db->groupBy('user_id');
        $this->db->orderBy('COUNT(ups.id)', 'DESC');
        $this->db->join('users u', 'ups.user_id=u.id', 'INNER');
        $columns = 
            ['user_id as most_ups_with_user_id', 'username as most_ups_with_username', 
                'COUNT(ups.id) AS most_ups_with_tot', 
                // 'group_concat(ppLeague_id) as most_ups_with_ppl_grpcnct',
                // 'group_concat(ppCupGroup_id) as most_ups_with_ppcg_grpcnct'
            ];
        $mostParticipations = $this->db->get('userParticipations ups', 10, $columns);

        return $mostParticipations;
    }

    public function saveWrapped(array $data){
        if(!$result= $this->db->insert('statsWrapped', $data)){
            print_r($this->db->getLastError());
        }
        return $result;
    }

    public function getWrapped(int $userId, int $year = 2023){
        $query = "SELECT 
                * 
              FROM (
                SELECT 
                    *,
                    RANK() OVER (ORDER BY tot_points DESC) as tot_points_rank,
                    RANK() OVER (ORDER BY tot_locks DESC) as tot_locks_rank,
                    RANK() OVER (ORDER BY tot_preso DESC) as tot_preso_rank,
                    RANK() OVER (ORDER BY tot_missed ASC) as tot_missed_rank,
                    RANK() OVER (ORDER BY avg_points DESC) as avg_points_rank,
                    RANK() OVER (ORDER BY perc_unox2 DESC) as perc_unox2_rank,
                    RANK() OVER (ORDER BY perc_ggng DESC) as perc_ggng_rank,
                    RANK() OVER (ORDER BY perc_uo25 DESC) as perc_uo25_rank
                FROM 
                    statsWrapped where stats_year =".$year."
              ) as ranked_users
              CROSS JOIN (
                SELECT COUNT(DISTINCT user_id) as ranked_users FROM statsWrapped
              ) as count_users_ranked
              WHERE 
                user_id = ?";

        $params = [$userId]; // Parameters to bind to the query
        $result = $this->db->rawQuery($query, $params);
        if($result) return $result[0];
    }
}
