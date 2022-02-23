<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Match;

final class MatchRepository extends BaseRepository
{   
    //TODO change to OO : Match
    public function getMatch(int $matchId) {
        $this->getDb()->where('id',$matchId);
        $match = $this->getDb()->getOne('matches');
        if (! $match) {
            throw new \App\Exception\Match('Match not found.', 404);
        }                           

        return $match;
    }
}
