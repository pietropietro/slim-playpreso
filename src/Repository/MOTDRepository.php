<?php

declare(strict_types=1);

namespace App\Repository;

final class MOTDRepository extends BaseRepository
{
    public function getCurrentMotd(){
        $this->db->join('guesses g', 'pprm.id = g.ppRoundMatch_id', 'LEFT');
        $this->db->groupBy('pprm.id');
        $this->db->orderBy('motd');
        
        if(date('H',time())<7){
            $this->db->where('motd', date('Y-m-d'), '<');
        }else{
            $this->db->where('motd', date('Y-m-d'), '<=');
        }

        $this->db->where('motd is not null');
        return $this->db->getOne('ppRoundMatches pprm', 'pprm.*, count(g.id) as aggr_count');
    }

    public function getMotd(?string $dateString = null){
        $dateString = $dateString ?? date('Y-m-d');
        $this->db->where('motd', $dateString);
        return $this->db->getOne('ppRoundMatches');
    }
    
    public function hasMotd(?string $dateString = null){
        $dateString = $dateString ?? date('Y-m-d');
        $this->db->where('motd', $dateString);
        return $this->db->has('ppRoundMatches');
    }

    public function create(int $matchId){
        $this->db->where('id', $matchId);
        $match = $this->db->getOne('matches');
        $dateString = (new \DateTime($match['date_start']))->format('Y-m-d');

        $data = array(
            "match_id" => $matchId,
            "motd" => $dateString
	    );

        if(!$this->db->insert('ppRoundMatches',$data)){
            throw new \App\Exception\Mysql($this->db->getLastError(), 500);
        };
        return $this->db->getInsertId();
    }

    public function getWeeklyStandings(){
        $sql = 
            "SELECT user_id, username, sum(g.points) as tot_points, count(g.id) as tot_locked
            FROM guesses g 
            JOIN (
                SELECT pprm.id 
                FROM ppRoundMatches pprm 
                INNER JOIN matches m 
                ON pprm.match_id = m.id 
                WHERE motd IS NOT NULL 
                AND m.verified_at IS NOT NULL 
                ORDER BY motd DESC
                LIMIT 7
            ) pprm 
            ON g.ppRoundMatch_id = pprm.id
            INNER JOIN users u on g.user_id = u.id
            group by user_id
            order by tot_points desc limit 3
        ";
        return $this->db->query($sql);
    }

    

    // public function delete(int $id){
    //     if(!$id) return;
    //     $this->db->where('id', $id);
    //     return $this->db->delete('ppRoundMatches',1);
    // }

}
