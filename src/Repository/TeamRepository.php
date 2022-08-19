<?php

declare(strict_types=1);

namespace App\Repository;

final class TeamRepository extends BaseRepository
{
    public function get(array $ids){
        $this->getDb()->where('id',$ids,'IN');
        return $this->getDb()->get('teams');
    }

    public function getOne(int $id){
        $this->getDb()->where('id',$id);
        return $this->getDb()->getOne('teams');
    }

    public function idFromExternal(int $ls_id) : int{
        $this->getDb()->where('ls_id',$ls_id);
        $team = $this->getDb()->getOne('teams');
        return $team['id'];
    }
}
