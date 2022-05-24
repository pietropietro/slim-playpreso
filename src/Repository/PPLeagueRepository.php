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

    public function startedIds(){
        $this->getDb()->where('started_at IS NOT NULL');
        return $this->getDb()->getValue('ppLeagues', 'id', null);
    }

    function getOne($id){
        $this->getDb()->where('id',$id);
        return $this->getDb()->getOne('ppLeagues');
    }

    function updateValue(int $id, string $column, $value){
        $data = array(
            $column => $value,
        );
        $this->getDb()->where('id',$id);
        $this->getDb()->update('ppLeagues', $data);
    }
}