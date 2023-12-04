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

    public function lastPreso(){
        $sql=" SELECT guesses.*, u.username 
            from guesses 
            inner join users u on guesses.user_id = u.id 
            where PRESO = 1 
            and guesses.match_id = (select match_id from guesses where PRESO = 1 order by verified_at desc limit 1)";
        return $this->db->query($sql);
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
        ?int $year=null
    ){
        if($ppRoundMatchId)$this->db->where('ppRoundMatch_id', $ppRoundMatchId);
        if($userId)$this->db->where('user_id', $userId);
        if($year)$this->db->where('YEAR(guessed_at)', $year);
        $this->db->orderBy('occurrances');
        $this->db->groupBy('result');
        return $this->db->getOne('guesses','concat_ws("-",home,away) as result, count(*) as occurrances');
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
    public function getUserCommonTeams(int $userId, int $year){
        $sql = "
            SELECT t.id, t.name, COUNT(*) as occurrences, round(AVG(subquery.points),1) as avg_points, SUM(subquery.preso) as cnt_preso
            FROM (
                SELECT home_id as team_id, g.points, g.preso FROM guesses g
                JOIN matches m ON g.match_id = m.id
                WHERE g.user_id = ? AND YEAR(g.guessed_at) = ?
                UNION ALL
                SELECT away_id as team_id, g.points, g.preso FROM guesses g
                JOIN matches m ON g.match_id = m.id
                WHERE g.user_id = ? AND YEAR(g.guessed_at) = ?
            ) as subquery
            JOIN teams t ON subquery.team_id = t.id
            GROUP BY t.id, t.name
            ORDER BY occurrences DESC
            LIMIT 3
        ";

        $params = [$userId, $year, $userId, $year]; 
        return $this->db->rawQuery($sql, $params);
    }

    // [0] => Array
    // (
    //     [id] => 216
    //     [name] => AZ Alkmaar
    //     [avg_points] => 9.8
    //     [occurrences] => 5
    // )
    public function getUserHighestAverageTeams(int $userId, int $year){
        $sql = "
            SELECT t.id, t.name, round(AVG(subquery.points),1) as avg_points, COUNT(*) as occurrences
            FROM (
                SELECT home_id as team_id, g.points FROM guesses g
                JOIN matches m ON g.match_id = m.id
                WHERE g.user_id = ? AND YEAR(g.guessed_at) = ?
                UNION ALL
                SELECT away_id as team_id, g.points FROM guesses g
                JOIN matches m ON g.match_id = m.id
                WHERE g.user_id = ? AND YEAR(g.guessed_at) = ?
            ) as subquery
            JOIN teams t ON subquery.team_id = t.id
            GROUP BY t.id, t.name
            HAVING COUNT(*) >= ?
            ORDER BY avg_points DESC
            LIMIT 3
        ";
        
        $limit = 5;
        $params = [$userId, 2023, $userId, 2023, 5]; // Replace $userId with the actual user ID
        return $this->db->rawQuery($sql, $params);
    }


    //     Array
    // (
        // [0] => Array
        // (
        //     [locks] => 705
        //     [avg_points] => 5.0
        //     [percentage_unox2] => 40.9
        //     [percentage_ggng] => 46.4
        //     [percentage_uo25] => 50.4
        //     [count_preso] => 71
        // )

    //stats only for locked guesses
    public function getUserMainSummary(int $userId, int $year){
        $this->db->where('user_id', $userId);
        $this->db->where('YEAR(guessed_at)', $year, '=');
        $this->db->groupBy('user_id'); // Group by user_id if you want to aggregate over all records of the user
        $columns = [
            'COUNT(*) AS locks',
            'round(avg(points),1) as avg_points',
            'round(100 * SUM(UNOX2 = 1) / COUNT(*),1) AS percentage_unox2',
            'round(100 * SUM(GGNG = 1) / COUNT(*),1) AS percentage_ggng',
            'round(100 * SUM(UO25 = 1) / COUNT(*),1) AS percentage_uo25',
            'SUM(PRESO = 1) AS count_preso',
        ];
        return $this->db->get('guesses', null, $columns);
    }


    public function getUserMissedCount(int $userId, int $year){
        $this->db->where('user_id', $userId);
        $this->db->where('YEAR(created_at)', $year, '=');
        $this->db->groupBy('user_id');
        $columns = ['SUM(guessed_at IS NULL) AS count_missed'];
        return $this->db->get('guesses', null, $columns);
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
    public function getUserLeagues(int $userId, int $year, bool $commonBestFlag = true){
        $this->db->join("matches m", "g.match_id = m.id", "INNER");
        $this->db->join("leagues l", "m.league_id = l.id", "INNER");

        $this->db->where("g.user_id", $userId);
        $this->db->where("YEAR(g.guessed_at)", $year, '=');

        $this->db->groupBy("m.league_id");

        if($commonBestFlag) $this->db->orderBy("COUNT(*)", "DESC");
        
        else{
            $this->db->orderBy("AVG(g.points)", "DESC");
            $this->db->having('total_guesses', 5, '>=');
        }

        $columns = ["l.id, l.name as league_name", "COUNT(*) as total_guesses", "AVG(g.points) as avg_points"];
        return $this->db->get("guesses g", 3 ,$columns);
    }

    function getExtremeMonth(int $userId, int $year, bool $isBest = true) {
    
        // Choose the order based on whether we want the best or worst month
        $orderByDirection = $isBest ? 'DESC' : 'ASC';
    
        $this->db->where('user_id', $userId);
        $this->db->where('guessed_at', null, 'IS NOT');
        $this->db->where("YEAR(guessed_at)", $year, '=');
        $this->db->groupBy('YEAR(guessed_at), MONTH(guessed_at)');
        $this->db->having('COUNT(id)', 10, '>=');
        $this->db->orderBy('AVG(points)', $orderByDirection);
    
        $columns = ['YEAR(guessed_at) AS year, 
                    MONTH(guessed_at) AS month, 
                    AVG(points) AS avg_points',
                    'count(id) as locks',
                    'sum(preso) as cnt_preso'
                ];

        return $this->db->getOne('guesses', $columns);
    }

}

