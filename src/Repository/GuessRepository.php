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

    public function getUserGuesses(int $userId, $verified = true, $limit = 20, string $stringTime = null) : array {
        
        $this->db->where('user_id', $userId);
        if($verified){
            $this->db->where('verified_at IS NOT NULL');
        }
        $this->db->orderBy('created_at', 'DESC');
        
        //i.e. "-3 months"
        if($stringTime){
            $this->db->where('verified_at', date("Y-m-d H:i:s", strtotime($stringTime)), ">");
        }
        return $this->db->get('guesses', $limit);
    }

    public function getForPPRoundMatch($ppRMId){
        $this->db->join("users u", "u.id=g.user_id", "INNER");
        $this->db->orderBy('g.points','desc');
        $this->db->orderBy('g.home','asc');
        $this->db->where('ppRoundMatch_id', $ppRMId);
        return $this->db->query("SELECT g.*, u.username FROM guesses g");
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

    //TODO MOVE TO SERVICE
    //TODO CHANGE COLUMN TO ENUM ['league_id', 'cup_group_id']
    public function getUpPoints(int $userId, string $column, int $valueId) : int {
        $ids = $this->db->subQuery();
        $ids->where($column, $valueId);
        $ids->get('ppRounds', null, 'id');

        $this->db->where('ppRound_id',$ids,'IN');
        
        if($matchList = $this->db->getValue('ppRoundMatches','id',null)){
            $this->db->where('user_id',$userId);
            $this->db->where('ppRoundMatch_id', $matchList,'in');
            $this->db->where("points IS NOT NULL");

            if($points = $this->db->getValue('guesses','sum(points)',null)){
                if($points[0]== null){
                    return 0;
                }
                return (int)$points[0];
            }
        }
        return 0;
    }

    //TODO MOVE TO SERVICE
    //TODO CHANGE COLUMN TO ENUM ['cup_id', 'league_id',]
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

}
