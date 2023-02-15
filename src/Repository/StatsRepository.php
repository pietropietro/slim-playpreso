<?php

declare(strict_types=1);

namespace App\Repository;

final class StatsRepository extends BaseRepository
{   

    public function bestUsers(?int $userId=null){

        if($userId){
            $this->db->where('g.user_id', $userId);
        }
        $limit = 5;
        $from = date("Y-m-d H:i:s", strtotime('- 1 month'));
        $this->db->where('verified_at', $from, '>');
        $this->db->join('users u', 'g.user_id=u.id', 'INNER');
        $this->db->groupBy('g.user_id');
        $this->db->orderBy('avg');
        $this->db->having('cnt', 9, '>');
        $columns = array('g.user_id', 
            'u.username', 
            'ROUND(avg(coalesce(g.points,0)),1) as avg',
            'count(*) as cnt',
            'count(guessed_at) as cnt_locked',
            'sum(PRESO) as cnt_preso',
            'sum(UNOX2) as cnt_1x2', 
            'sum(UO25) as cnt_uo25', 
            'sum(GGNG) as cnt_ggng'
        );
        return $this->db->get('guesses g', $limit, $columns);
    }

    public function lastPreso(){
        $sql='
        select guesses.*, u.username 
        from guesses 
        inner join users u on guesses.user_id = u.id 
        where PRESO = 1 
        and guesses.match_id = (select match_id from guesses where PRESO = 1 order by verified_at desc limit 1)';
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
    
}
