<?php

declare(strict_types=1);

namespace App\Repository;

final class PPRoundMatchRepository extends BaseRepository
{
    public function getAllRound($ppRoundId){
        $this->getDb()->where('ppRound_id', $ppRoundId);
        return $this->getDb()->get('ppRoundMatches');
    }
}
