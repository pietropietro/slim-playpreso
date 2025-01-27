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

    public function getFromMatch(int $matchId){
        $this->db->where('match_id', $matchId);
        $this->db->where('motd is not null');
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
        $chart = $this->db->withTotalCount()->get(
            "guesses", 
            [$offset, $limit], 
            "guesses.user_id, 
            SUM(guesses.points) as tot_points,
            count(guesses.id) as tot_locked,
            sum(preso) as tot_preso, 
            sum(UNOX2) as tot_unox2,
            group_concat(motd) as concat_motd,
            group_concat(COALESCE(points,0)) as concat_points
            "
        );
        
        return [
            'chart' => $chart,
            'total' => (int) $this->db->totalCount,
        ];
    }

    public function getLastForUser(int $userId, ?int $howMany=30){
        $this->db->orderBy('motd', 'desc');
        $ids = $this->db->getValue('ppRoundMatches', 'id', 30);

        $this->db->join('ppRoundMatches pprm', 'pprm.id=guesses.ppRoundMatch_id', 'INNER');
        $this->db->where('ppRoundMatch_id', $ids, 'IN');
        $this->db->where('user_id', $userId);
        $this->db->orderBy('verified_at', 'desc');

        return $this->db->get('guesses',null,'guesses.*, match_id');
    }
    


}
