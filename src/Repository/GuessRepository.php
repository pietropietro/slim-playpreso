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
        if($stringTime){
            //i.e. "-3 months"
            $this->getDb()->where('verified_at', date("Y-m-d H:i:s", strtotime($stringTime)), ">");
        }
        return $this->getDb()->get('guesses', $limit);
    }

    
    public function userScore($userId,string $column, int $valueId) : int {
        $ids = $this->getDb()->subQuery();
        $ids->where($column, $valueId);
        $ids->get('ppRounds', null, 'id');

        $this->getDb()->where('ppRound_id',$ids,'IN');
        
        if($list = $this->getDb()->getValue('ppRoundMatches','id',null)){
            $this->getDb()->where('user_id',$userId);
            $this->getDb()->where('ppRoundMatch_id', $list,'in');
            $this->getDb()->where("score != ".$_SERVER['VIRGIN_GUESS_SCORE']);

            if($score = $this->getDb()->getValue('guesses','sum(score)',null)){
                if($score[0]== null){
                    return 0;
                }
                return (int)$score[0];
            }
        }
        return 0;
    }

}
