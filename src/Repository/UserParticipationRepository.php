<?php

declare(strict_types=1);

namespace App\Repository;

final class UserParticipationRepository extends BaseRepository
{
    function getParticipations(int $userId) : array {
        $this->getDb()->where('user_id',$userId);
        $this->getDb()->orderBy('joined_at','desc');
        $placements = $this->getDb()->get('userParticipations');
        
        return $placements;
    }

    function getUserPPLeagueIds(int $userId, bool $active){
        $this->getDb()->where('user_id', $userId);
        if($active){
            $this->getDb()->where('placed_at IS NULL');
        }
        $this->getDb()->orderBy('joined_at','desc');
        $this->getDb()->where('ppLeague_id IS NOT NULL');
        $ids = $this->getDb()->getValue('userParticipations', 'ppLeague_id', null);
        return $ids;
    }

    function getPromotedPPLeagueTypeIds(int $userId){
        $this->getDb()->where('user_id',$userId);
        $this->getDb()->where('placement', $_SERVER['PPLEAGUE_QUALIFYING_POSITION'], "<=");

        $promotedPPLeagueTypeIds = $this->getDb()->getValue('userParticipations','ppLeagueType_id',null);
        return $promotedPPLeagueTypeIds;
    }

    function getCurrentPPLeagueTypeIds(int $userId){
        $this->getDb()->groupBy('ppLeagueType_id');
        $this->getDb()->where('user_id',$userId);
        $this->getDb()->where('placed_at IS NULL');
        $this->getDb()->where('ppLeagueType_id IS NOT NULL');
        return $this->getDb()->getValue('userParticipations', 'ppLeagueType_id', null);
    }

    function getUserIds(int $ppLeagueId){
        $this->getDb()->where('ppLeague_id',$ppLeagueId);
        return $this->getDb()->getValue('userParticipations','user_id',null);
    }

}