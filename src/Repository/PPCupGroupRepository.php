<?php

declare(strict_types=1);

namespace App\Repository;

use \App\Exception\NotFound;

final class PPCupGroupRepository extends BaseRepository
{
    public function get(array $ids) {
        $this->getDb()->where('id', $ids, 'IN');
        $ppCupGroups=$this->getDb()->get('ppCupGroups');
        if (! $ppCupGroups) {
            throw new NotFound('ppCupGroups not found.', 404);
        }   
        return $ppCupGroups;
    }

    function getOne(int $id){
        $this->getDb()->where('id', $id);
        return $this->getDb()->getOne('ppCupGroups');
    }

    function getCupGroupIds(int $ppCupId){
        $this->getDb()->where('ppCup_id', $ppCupId);
        return $this->getDb()->getValue('ppCupGroups', 'id', null);
    }

    function getGroupsForCup(int $ppCupId){
        $this->getDb()->where('ppCup_id', $ppCupId);
        return $this->getDb()->get('ppCupGroups');
    }
}