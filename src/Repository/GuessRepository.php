<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Guess;

final class GuessRepository extends BaseRepository
{
    public function getUserGuesses(int $userId, $verified = true, $limit = 20, string $stringTime = null) : array {
        
        $this->getDb()->where('user_id', $userId);
        if($verified){
            $this->getDb()->where('verified_at', 'NOT NULL');
        }
        $this->getDb()->orderBy('created_at', 'DESC');
        if($stringTime){
            //i.e. "-3 months"
            $this->getDb()->where('verified_at', date("Y-m-d H:i:s", strtotime($stringTime)), ">");
        }
        return $this->getDb()->get('guesses', $limit);
    }

    //TODO
    // public function getGuessesForMatch() : array {

    // }
}
