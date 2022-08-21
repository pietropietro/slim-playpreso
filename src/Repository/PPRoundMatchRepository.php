<?php

declare(strict_types=1);

namespace App\Repository;

final class PPRoundMatchRepository extends BaseRepository
{
    public function get(int $ppRoundId){
        $this->getDb()->where('ppRound_id', $ppRoundId);
        return $this->getDb()->get('ppRoundMatches');
    }

    public function getRoundIdsWithMatch(int $matchId){
        $this->getDb()->where('match_id', $matchId);
        return $this->getDb()->getValue('ppRoundMatches', 'ppRound_id');
    }
}
