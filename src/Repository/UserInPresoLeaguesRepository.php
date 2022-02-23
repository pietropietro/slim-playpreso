<?php

declare(strict_types=1);

namespace App\Repository;

final class UserInPresoLeaguesRepository extends BaseRepository
{
    //TODO CHANGE W/ new db schema
    // function getUserPresoLeagueIds($userId, $active = false) : array {
    //     $this->getDb()->where('user_id',$userId);
    //     $this->getDb()->orderBy('joined_at','desc');
    //     if($active){
    //         $this->getDb()->where('endAck',0);
    //     }
    //     $presoLeagueIDs = $this->getDb()->getValue('usersInPresoLeagues','presoLeague_id',null);
    //     return $presoLeagueIDs;
    // }

}