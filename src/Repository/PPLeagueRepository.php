<?php

declare(strict_types=1);

namespace App\Repository;

use \App\Exception\NotFound;

final class PPLeagueRepository extends BaseRepository
{
    public function get(array $ids) {
        $this->getDb()->where('id', $ids, 'IN');
        $ppLeagues=$this->getDb()->get('ppLeagues');
        if (! $ppLeagues) {
            throw new NotFound('ppLeagues not found.', 404);
        }   
        return $ppLeagues;
    }

    function getJoinable($typeId){
        $this->getDb()->where('ppLeagueType_id', $typeId);
        $this->getDb()->where('started_at IS NULL');
        $this->getDb()->where('user_count', 20, "<");
       
        return $this->getDb()->getOne('ppLeagues');
    }

    function getOne($id){
        $this->getDb()->where('id',$id);
        return $this->getDb()->getOne('ppLeagues');
    }


    public function startedIds(){
        $this->getDb()->where('started_at IS NOT NULL');
        return $this->getDb()->getValue('ppLeagues', 'id', null);
    }
    
    function updateValue(int $id, string $column, $value){
        $data = array(
            $column => $value,
        );
        $this->getDb()->where('id',$id);
        $this->getDb()->update('ppLeagues', $data);
    }

    function create($typeId){
        $data = array(
			"ppLeagueType_id" => $typeId,
			"created_at" => date("Y-m-d H:i:s"),
			"user_count" => 0,
            "round_count" => 0
	    );
        return $this->getDb()->insert('ppLeagues',$data);
    }

    function incrementUserCounter($id){
        $this->getDb()->query("UPDATE ppLeagues SET user_count=user_count+1 WHERE id=$id");
    }
}