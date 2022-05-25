<?php

declare(strict_types=1);

namespace App\Repository;

final class UserParticipationRepository extends BaseRepository
{
    function getParticipations(int $userId) : array {
        $this->getDb()->where('user_id',$userId);
        $this->getDb()->orderBy('joined_at','desc');
        $placements = $this->getDb()->get('userParticipations');
        
        return $placements;
    }

    //TODO change string type to ENUM 'ppLeague_id', 'ppCupGroup_id'
    function getParticipationsForUser(int $userId, string $type, bool $active){
        $this->getDb()->where('user_id', $userId);
        if($active){
            $this->getDb()->where('finished IS NULL');
        }
        $this->getDb()->orderBy('joined_at','desc');
        $this->getDb()->where($type.' IS NOT NULL');
        return $this->getDb()->get('userParticipations');
    }

    function getPromotedPPLeagueTypeIds(int $userId){
        $this->getDb()->where('user_id',$userId);
        $this->getDb()->where('finished',1);
        $this->getDb()->where('position', $_SERVER['PPLEAGUE_QUALIFYING_POSITION'], "<=");

        $promotedPPLeagueTypeIds = $this->getDb()->getValue('userParticipations','ppLeagueType_id',null);
        return $promotedPPLeagueTypeIds;
    }

    function getCurrentPPLeagueTypeIds(int $userId){
        $this->getDb()->groupBy('ppLeagueType_id');
        $this->getDb()->where('user_id',$userId);
        $this->getDb()->where('finished IS NULL');
        $this->getDb()->where('ppLeagueType_id IS NOT NULL');
        return $this->getDb()->getValue('userParticipations', 'ppLeagueType_id', null);
    }

    function getLeagueParticipations(int $ppLeagueId){
        $this->getDb()->join("users u", "u.id=up.user_id", "INNER");
        $this->getDb()->orderBy('up.joined_at','desc');
        $this->getDb()->where('ppLeague_id',$ppLeagueId);
        return $this->getDb()->query("SELECT up.*, u.username FROM userParticipations up");
    }

    function updateScore(int $id, int $score){
        $data = array(
			"score" => $score,
            "updated_at" => date("Y-m-d H:i:s"),
		);
        $this->getDb()->where('id',$id);
        $this->getDb()->update('userParticipations',$data);
    }

    function updatePosition(int $id, int $position){
        $data = array(
			"position" => $position,
            "updated_at" => date("Y-m-d H:i:s"),
		);
        $this->getDb()->where('id',$id);
        $this->getDb()->update('userParticipations',$data);
    }

}