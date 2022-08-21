<?php

declare(strict_types=1);

namespace App\Repository;

final class PPRoundMatchRepository extends BaseRepository
{
    public function get(int $ppRoundId){
        $this->db->where('ppRound_id', $ppRoundId);
        return $this->db->get('ppRoundMatches');
    }

    public function getRoundIdsForMatch(int $matchId){
        $this->db->where('match_id', $matchId);
        return $this->db->getValue('ppRoundMatches', 'ppRound_id');
    }
}
