<?php

declare(strict_types=1);

namespace App\Repository;

final class PPLeagueRepository extends BaseRepository
{
    public function getPPLeagues(array $ids) {
        $this->getDb()->where('id', $ids, 'IN');
        $ppLeagues=$this->getDb()->get('ppLeagues');
        if (! $ppLeagues) {
            throw new \App\Exception\PPLeague('ppLeagues not found.', 404);
        }   
        return $ppLeagues;
    }
}