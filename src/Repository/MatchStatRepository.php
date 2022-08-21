<?php

declare(strict_types=1);

namespace App\Repository;

final class MatchStatRepository extends BaseRepository
{   
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
