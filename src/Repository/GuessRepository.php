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

    public function getForUser(int $userId, ?bool $not_verified=null) : array {
        $this->db->where('user_id', $userId);
        $this->db->join('matches m ', 'm.id=guesses.match_id', 'INNER');
        if($not_verified){
            $this->db->where('guesses.verified_at IS NULL');
        }
        // $this->db->orderBy('guessed_at', 'DESC');
        $this->db->orderBy('m.date_start', 'DESC');

        //i.e. "-3 months"
        // if($stringTime){
        //     $this->db->where('verified_at', date("Y-m-d H:i:s", strtotime($stringTime)), ">");
        // }
        return $this->db->get('guesses', null, 'guesses.*');
    }

    public function getForPPRoundMatch(int $ppRMId, ?int $userId=null){
        $this->db->where('ppRoundMatch_id', $ppRMId);
        if($userId){
            $this->db->where('user_id', $userId);
            return $this->db->getOne('guesses');
        }
        $this->db->join("users u", "u.id=g.user_id", "INNER");
        $this->db->orderBy('g.points','desc');
        $this->db->orderBy('g.home','asc');
        return $this->db->get('guesses g', null, array('g.*, u.username'));
    }

    public function getForMatch(int $matchId, bool $not_verified){
        $this->db->where('match_id', $matchId);
        if($not_verified){
            $this->db->where('verified_at IS NULL');
        }
        return $this->db->get('guesses');
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
    public function countUpNumbers(int $userId, string $column, int $valueId) {
        $ids = $this->db->subQuery();
        $ids->where($column, $valueId);
        $ids->get('ppRounds', null, 'id');

        $this->db->where('ppRound_id',$ids,'IN');
        
        if($matchList = $this->db->getValue('ppRoundMatches','id',null)){
            $this->db->where('user_id',$userId);
            $this->db->where('ppRoundMatch_id', $matchList,'in');
            $this->db->where("verified_at IS NOT NULL");
            $this->db->where("guessed_at IS NOT NULL");
            $columns = array('sum(points) as tot_points', 'sum(preso) as tot_preso', 'sum(UNOX2) as tot_unox2', 'count(id) as tot_locked');

            if($upResult = $this->db->getOne('guesses', $columns)){
                return $upResult;
            }
        }
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

    public function verifyMissed(){
        $data = array(
            "g.verified_at" => $this->db->now()
        );

        $before = date("Y-m-d H:i:s", strtotime('+2 hours'));

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

}
