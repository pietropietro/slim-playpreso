<?php

declare(strict_types=1);

namespace App\Repository;

final class PresoLeagueRepository extends BaseRepository
{
    public function getPresoLeagues(array $ids) {
        $this->getDb()->where('id', $ids, 'IN');
        $presoLeagues=$this->getDb()->get('presoLeagues');
        if (! $presoLeagues) {
            throw new \App\Exception\PresoLeague('PresoLeagues not found.', 404);
        }   
        return $presoLeagues;
    }
}