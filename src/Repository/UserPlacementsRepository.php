<?php

declare(strict_types=1);

namespace App\Repository;

//TODO merge with userParticipations
final class UserPlacementsRepository extends BaseRepository
{
    function getPlacements($userId) : array {
        $this->getDb()->where('user_id',$userId);

        $placements = $this->getDb()->get('userPlacements');
        
        return $placements;
    }

}