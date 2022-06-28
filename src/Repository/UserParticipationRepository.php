<?php

declare(strict_types=1);

namespace App\Repository;

final class UserParticipationRepository extends BaseRepository
{   
    private $tableName = 'userParticipations';

    function create(int $userId, array $columns, array $valueIds){
        $data = array(
			"user_id" => $userId,
			"joined_at" => date("Y-m-d H:i:s"),
	    );
        foreach($columns as $ind => $col){
            $data[$col] = $valueIds[$ind];
        }
        return $this->getDb()->insert($this->tableName, $data);
    }

    //TODO change string type to ENUM 'ppLeague_id', 'ppCupGroup_id'
    function getUserParticipations(int $userId, ?string $type, ?bool $active, ?int $minPosition){
        $this->getDb()->where('user_id', $userId);
        if($active){
            $this->getDb()->where('finished IS NULL');
        }
        if($minPosition){
            $this->getDb()->where('position', $minPosition, '<=');
        }
        if($type){
            $this->getDb()->where($type.' IS NOT NULL');
        }
        $this->getDb()->orderBy('joined_at','desc');
        return $this->getDb()->get($this->tableName) ;
    }

    function getTournamentParticipations(string $type, int $valueId){
        $this->getDb()->join("users u", "u.id=up.user_id", "INNER");
        $this->getDb()->orderBy('up.position','asc');
        $this->getDb()->where($type, $valueId);
        return $this->getDb()->query("SELECT up.*, u.username FROM userParticipations up");
    }


    function getPromotedPPLeagueTypeIds(int $userId){
        $this->getDb()->where('user_id',$userId);
        $this->getDb()->where('finished',1);
        $this->getDb()->where('position', $_SERVER['PPLEAGUE_TROPHY_POSITION'], "<=");

        $promotedPPLeagueTypeIds = $this->getDb()->getValue($this->tableName, 'ppLeagueType_id',null);
        return $promotedPPLeagueTypeIds;
    }

    function getCurrentPPLeagueTypeIds(int $userId){
        $this->getDb()->groupBy('ppLeagueType_id');
        $this->getDb()->where('user_id',$userId);
        $this->getDb()->where('finished IS NULL');
        $this->getDb()->where('ppLeagueType_id IS NOT NULL');
        return $this->getDb()->getValue($this->tableName,  'ppLeagueType_id', null);
    }

    
    function updateScore(int $id, int $score){
        $data = array(
			"score" => $score,
            "updated_at" => date("Y-m-d H:i:s"),
		);
        $this->getDb()->where('id',$id);
        $this->getDb()->update($this->tableName, $data);
    }

    function updatePosition(int $id, int $position){
        $data = array(
			"position" => $position,
            "updated_at" => date("Y-m-d H:i:s"),
		);
        $this->getDb()->where('id',$id);
        $this->getDb()->update($this->tableName, $data);
    }

}