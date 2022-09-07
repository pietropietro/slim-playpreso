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
        return $this->db->insert($this->tableName, $data);
    }

    //TODO change string type to ENUM 'ppLeague_id', 'ppCupGroup_id'
    function getUserParticipations(int $userId, ?string $type, ?bool $active, ?int $minPosition){
        $this->db->where('user_id', $userId);
        if($active){
            $this->db->where('finished IS NULL');
        }
        if($minPosition){
            $this->db->where('position', $minPosition, '<=');
        }
        if($type){
            $this->db->where($type.' IS NOT NULL');
        }
        $this->db->orderBy('joined_at','desc');
        return $this->db->get($this->tableName) ;
    }

    function getForTournament(string $tournamentColumn, int $tournamentId){
        $this->db->join("users u", "u.id=up.user_id", "INNER");
        $this->db->orderBy('up.position','asc');
        $this->db->where($tournamentColumn, $tournamentId);
        return $this->db->query("SELECT up.*, u.username FROM userParticipations up");
    }


    function getPromotedTournamentTypesForUser(int $userId, bool $include_ppCups = false, bool $return_id_only = true){
        $this->db->where('user_id',$userId);
        $this->db->where('finished',1);
        $this->db->where('position', $_SERVER['PPLEAGUE_TROPHY_POSITION'], "<=");
        if(!$include_ppCups) $this->db->where('ppLeague_id IS NOT NULL');
        if($return_id_only) return $this->db->getValue($this->tableName, 'ppTournamentType_id', null);
        return $this->db->get($this->tableName);
    }

    function getCurrentTournamentTypesForUser(int $userId, bool $include_ppCups = false, bool $return_id_only = true){
        $this->db->groupBy('ppTournamentType_id');
        $this->db->where('user_id',$userId);
        $this->db->where('finished IS NULL');
        if(!$include_ppCups) $this->db->where('ppLeague_id IS NOT NULL');
       
        if($return_id_only)return $this->db->getValue($this->tableName,  'ppTournamentType_id', null);
        return $this->db->get($this->tableName);
    }

    function getCupScoreTotal(int $userId, int $cupId, ?string $joinedAt) : ?int{
        $this->db->where('user_id',$userId);
        $this->db->where('ppCup_id',$cupId);
        if($joinedAt)$this->db->where('joined_at', $joinedAt, '<=');
        return (int)$this->db->getOne($this->tableName, 'sum(score) as score_total')['score_total'];
    }

    
    function updateScore(int $id, int $score){
        $data = array(
			"score" => $score,
            "updated_at" => $this->db->now(),
		);
        $this->db->where('id',$id);
        $this->db->update($this->tableName, $data);
    }

    function updatePosition(int $id, int $position){
        $data = array(
			"position" => $position,
            "updated_at" => $this->db->now(),
		);
        $this->db->where('id',$id);
        $this->db->update($this->tableName, $data);
    }

    public function setFinished(string $tournamentColumn, int $tournamentId){
        $data = array(
			"finished" => 1,
            "updated_at" => $this->db->now(),
		);
        $this->db->where($tournamentColumn, $id);
        $this->db->update($this->tableName, $data);
    }

    public function setStarted(string $tournamentColumn, int $tournamentId){
        $data = array(
			"score" => 0,
            "updated_at" => $this->db->now(),
		);
        $this->db->where($tournamentColumn, $id);
        $this->db->update($this->tableName, $data);
    }

}