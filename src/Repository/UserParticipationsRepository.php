<?php

declare(strict_types=1);

namespace App\Repository;

//TODO merge w/ userPlacements
final class UserParticipationsRepository extends BaseRepository
{
    //TODO CHANGE W/ new db schema
    // function getUserPPLeagueIds($userId, $active = false) : array {
    //     $this->getDb()->where('user_id',$userId);
    //     $this->getDb()->orderBy('joined_at','desc');
    //     if($active){
    //         $this->getDb()->where('endAck',0);
    //     }
    //     $ppLeagueIDs = $this->getDb()->getValue('usersInPPLeagues','ppLeague_id',null);
    //     return $ppLeagueIDs;
    // }

}