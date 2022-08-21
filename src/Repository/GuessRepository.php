<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Guess;

final class GuessRepository extends BaseRepository
{
    public function getUserGuesses(int $userId, $verified = true, $limit = 20, string $stringTime = null) : array {
        
        $this->getDb()->where('user_id', $userId);
        if($verified){
            $this->getDb()->where('verified_at IS NOT NULL');
        }
        $this->getDb()->orderBy('created_at', 'DESC');
        
        //i.e. "-3 months"
        if($stringTime){
            $this->getDb()->where('verified_at', date("Y-m-d H:i:s", strtotime($stringTime)), ">");
        }
        return $this->getDb()->get('guesses', $limit);
    }

    public function getForPPRoundMatch($ppRMId){
        $this->getDb()->join("users u", "u.id=g.user_id", "INNER");
        $this->getDb()->orderBy('g.score','desc');
        $this->getDb()->orderBy('g.guess_home','asc');
        $this->getDb()->where('ppRoundMatch_id', $ppRMId);
        return $this->getDb()->query("SELECT g.*, u.username FROM guesses g");
    }

    public function getForMatch(int $matchId, bool $not_verified){
        $this->getDb()->where('match_id', $matchId);
        if($not_verified){
            $this->getDb()->where('verified_at IS NULL');
        }
        return $this->getDb()->get('guesses');
    }

    public function verify(int $id, bool $unox2, bool $uo25, bool $ggng, bool $preso, int $score){
        $data = array(
            "UNOX2" => $unox2,
            "GGNG" => $ggng,
            "UO25" => $uo25,
            "PRESO" => $preso,
            "score" => $score,
            "verified_at" => $this->getDb()->now()
        );

        $db->where('id', $id);
        $this->getDb()->update('guesses', $data, 1);        
    }

    //TODO MOVE TO SERVICE
    //TODO CHANGE COLUMN TO ENUM ['league_id', 'cup_group_id']
    public function getUpScore(int $userId, string $column, int $valueId) : int {
        $ids = $this->getDb()->subQuery();
        $ids->where($column, $valueId);
        $ids->get('ppRounds', null, 'id');

        $this->getDb()->where('ppRound_id',$ids,'IN');
        
        if($matchList = $this->getDb()->getValue('ppRoundMatches','id',null)){
            $this->getDb()->where('user_id',$userId);
            $this->getDb()->where('ppRoundMatch_id', $matchList,'in');
            $this->getDb()->where("score IS NOT NULL");

            if($score = $this->getDb()->getValue('guesses','sum(score)',null)){
                if($score[0]== null){
                    return 0;
                }
                return (int)$score[0];
            }
        }
        return 0;
    }

    //TODO MOVE TO SERVICE
    //TODO CHANGE COLUMN TO ENUM ['cup_id', 'league_id',]
    public function hasUnlockedGuesses(int $userId, string $column, int $valueId){
        $ppRoundIds = $this->getDb()->subQuery();
        $ppRoundIds->where($column, $valueId);
        $ppRoundIds->get('ppRounds',null,'id');
        
        $ppRoundMatchIds = $this->getDb()->subQuery();
        $ppRoundMatchIds->where('ppRound_id', $ppRoundIds, 'IN');
        $ppRoundMatchIds->get('ppRoundMatches',null,'id');

        $this->getDb()->where('ppRoundMatch_id', $ppRoundMatchIds, 'IN');
        $this->getDb()->where('guessed_at IS NULL');
        $this->getDb()->where('verified_at IS NULL');
        
        $result = $this->getDb()->getOne('guesses');
        return !!$result;

    }

}
