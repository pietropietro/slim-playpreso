<?php

declare(strict_types=1);

namespace App\Repository;

final class MOTDRepository extends BaseRepository
{

    public function get(?bool $verified = null, int $offset = 0, int $limit = 10){
        $this->db->join('matches m', 'm.id=pprm.match_id', 'inner');
        if($verified){
            $this->db->where('verified_at is not null');
        }
        $this->db->where('motd < now()');
        $this->db->orderBy('motd', 'desc');
        return $this->db->get('ppRoundMatches pprm', [$offset, $limit], 'pprm.*');
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

    public function retrieveMotdChart(?int $offset = 0, ?int $limit = 10) {
        // Ensure offset is non-negative
        $offset = max(0, $offset);

        $dateAgo = date("Y-m-d", strtotime('-30 days'));
    
        $this->db->join("ppRoundMatches pprm", "pprm.id = guesses.ppRoundMatch_id", "INNER");
        $this->db->where("pprm.motd", $dateAgo, ">=");
        $this->db->groupBy("guesses.user_id");
        // Order by tot_wins, net_prize, and tot_points
        $this->db->orderBy("tot_wins", "desc");
        $this->db->orderBy("tot_prize", "desc");
        $this->db->orderBy("tot_points", "desc");
        
        $chart = $this->db->withTotalCount()->get(
            "guesses", 
            [$offset, $limit], 
            "   guesses.user_id, 
            SUM(guesses.winner) as tot_wins,
            COALESCE(SUM(guesses.winner_prize), 0) as tot_prize, 
            SUM(guesses.points) as tot_points,
            COUNT(guesses.id) as tot_locked,
            SUM(PRESO) as tot_preso, 
            SUM(UNOX2) as tot_unox2"
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
