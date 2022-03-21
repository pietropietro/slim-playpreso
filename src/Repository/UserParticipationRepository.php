<?php

declare(strict_types=1);

namespace App\Repository;

final class UserParticipationRepository extends BaseRepository
{

    function getOkPPLeagueTypeIdsForUser($userId){
        $this->getDb()->where('user_id',$userId);
        $this->getDb()->where('placement', $_SERVER['PPLEAGUE_QUALIFYING_POSITION'], "<=");

        $passedPPLeagueTypeIds = $this->getDb()->get('userParticipations',null,'ppLeagueType_id');

        return array_column($passedPPLeagueTypeIds,'ppLeagueType_id');
    }

    

    //TODO fix db query
    function getPlacements($userId) : array {
        $this->getDb()->where('user_id',$userId);

        $placements = $this->getDb()->get('userParticipations');
        
        return $placements;
    }

}