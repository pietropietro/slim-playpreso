<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Guess;

final class GuessRepository extends BaseRepository
{
    public function getGuessesForUser(id $userId, $verified = true) : array {
        
        $this->getDb()->where('user_id', $userid);
        $this->getDb()->where('verified', $verified);
        $this->getDb()->orderBy('created_at', 'DESC');
        $this->getDb()->where('verified_at', date("Y-m-d H:i:s", strtotime("-3 months")), ">");
    
        return $this->getDb()->get('guesses');
        
    }

    //TODO
    // public function getGuessesForMatch() : array {

    // }
}
