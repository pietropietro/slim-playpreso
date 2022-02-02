<?php

declare(strict_types=1);

namespace App\Repository;

final class UserInPresoLeaguesRepository extends BaseRepository
{
    function getPresoLeagueIDsFromUser($userId,$active) : array {
        $this->getDb()->where('user_id',$userId);
        $this->getDb()->orderBy('joined_at','desc');
        $presoLeagueIDs = $this->getDb()->getValue('usersInPresoLeagues','presoLeague_id',null);
        return $presoLeagueIDs;
    }

}