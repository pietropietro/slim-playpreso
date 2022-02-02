<?php

declare(strict_types=1);

namespace App\Repository;

final class TrophyRepository extends BaseRepository
{
    function getTrophies($userId) : array {
        $this->getDb()->where('user_id',$userId);

        //TODO RENAME DB TABLE CAMEL CASE 
        //TODO RENAME COLUMNS NAME -> position, plId
        $trophies = $this->getDb()->get('usersPLplacements');
        
        return $trophies;
    }

}