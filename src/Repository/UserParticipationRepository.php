<?php

declare(strict_types=1);

namespace App\Repository;

final class UserParticipationRepository extends BaseRepository
{

    //TODO test before, then use this!
    function getOkPPLeagueTypeIdsForUser($userId){
        $this->getDb()->where('user_id',$userId);
        $this->getDb()->where('placement', $_SERVER['PPLEAGUE_QUALIFYING_POSITION'], "<=");

        $passedPPLeagueTypeIds = $this->getDb()->get('userParticipations',null,'ppLeagueType_id');

        return array_column($passedPPLeagueTypeIds,'ppLeagueType_id');
    }

    function getParticipations($userId) : array {
        $this->getDb()->where('user_id',$userId);
        $this->getDb()->orderBy('joined_at','desc');
        $placements = $this->getDb()->get('userParticipations');
        
        return $placements;
    }

    function getUserPPLeagueIds($userId, $active){
        $this->getDb()->where('user_id', $userId);
        if($active){
            $this->getDb()->where('placed_at IS NULL');
        }
        $this->getDb()->orderBy('joined_at','desc');
        $this->getDb()->where('ppLeague_id IS NOT NULL');
        $ids = $this->getDb()->getValue('userParticipations', 'ppLeague_id', null);
        return $ids;
    }

}