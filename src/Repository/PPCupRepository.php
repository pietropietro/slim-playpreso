<?php

declare(strict_types=1);

namespace App\Repository;

use \App\Exception\NotFound;

final class PPCupRepository extends BaseRepository
{
    public function get(array $ids) {
        $this->getDb()->where('id', $ids, 'IN');
        $ppCups=$this->getDb()->get('ppCups');
        if (! $ppCups) {
            throw new NotFound('ppCups not found.', 404);
        }   
        return $ppCups;
    }

    function getOne(int $id){
        $this->getDb()->where('id', $id);
        return $this->getDb()->getOne('ppCups');
    }
}