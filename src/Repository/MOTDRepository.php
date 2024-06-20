<?php

declare(strict_types=1);

namespace App\Repository;

final class MOTDRepository extends BaseRepository
{
    public function getLatestMotds(?int $limit = 7){
        $this->db->join('guesses g', 'pprm.id = g.ppRoundMatch_id', 'LEFT');
        $this->db->groupBy('pprm.id');
        $this->db->orderBy('motd');
        
        if(date('H',time())<7){
            $this->db->where('motd', date('Y-m-d'), '<');
        }else{
            $this->db->where('motd', date('Y-m-d'), '<=');
        }

        $this->db->where('motd is not null');
        return $this->db->get('ppRoundMatches pprm', $limit,'pprm.*, count(g.id) as aggr_count');
    }

    public function getMotd(?string $dateString = null){
        if(!$dateString){
            $dateString = date('H',time()) < 7 ? date('Y-m-d', strtotime('-1 days')) : date('Y-m-d');
        };

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

    // public function getWeeklyStandings(?int $userId, int $limit=6){
    //     $sql = 
    //         "SELECT user_id, username, sum(g.points) as tot_points, count(g.id) as tot_locked
    //         FROM guesses g
    //         JOIN (
    //             SELECT pprm.id 
    //             FROM ppRoundMatches pprm 
    //             INNER JOIN matches m 
    //             ON pprm.match_id = m.id 
    //             WHERE motd IS NOT NULL 
    //             AND m.verified_at IS NOT NULL 
    //             ORDER BY motd DESC
    //             LIMIT 7
    //         ) pprm 
    //         ON g.ppRoundMatch_id = pprm.id
    //         INNER JOIN users u on g.user_id = u.id".
    //         ($userId ? ' WHERE user_id='.$userId.' ' : ' ')
    //         ." group by user_id
    //         order by tot_points desc limit ".$limit;
    //     return $this->db->query($sql);
    // }

    public function insertLeader(int $userId, int $points){
        $data = array(
            'user_id' => $userId,
            'tot_points' => $points,
            'calculated_at' => $this->db->now()
        );
        return $this->db->insert('motdLeader', $data);
    }

    public function getMotdLeader(){
        $this->db->orderBy('calculated_at', 'desc');
        return $this->db->getOne('motdLeader');
    }

    public function retrieveMotdChart($offset = 0, $limit = 10) {
        $dateAgo = date("Y-m-d", strtotime('-1 month'));
    
        $this->db->join("ppRoundMatches pprm", "pprm.id = guesses.ppRoundMatch_id", "INNER");
        $this->db->where("pprm.motd", $dateAgo, ">=");
        $this->db->groupBy("guesses.user_id");
        $this->db->orderBy("tot_points", "desc");
        $chart = $this->db->withTotalCount()->get("guesses", [$offset, $limit], "guesses.user_id, SUM(guesses.points) as tot_points");
        
        return [
            'chart' => $chart,
            'total' => (int) $this->db->totalCount,
        ];
    }

    

    // public function delete(int $id){
    //     if(!$id) return;
    //     $this->db->where('id', $id);
    //     return $this->db->delete('ppRoundMatches',1);
    // }

}
