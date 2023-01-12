<?php

declare(strict_types=1);

namespace App\Repository;

final class UserParticipationRepository extends BaseRepository
{   
    private $tableName = 'userParticipations';
    private $columnsJoined3= 'userParticipations.*, 
                            if(ppl.started_at IS NOT NULL or ppcg.started_at IS NOT NULL, 1, 0) as started, 
                            if(ppl.finished_at IS NOT NULL or ppcg.finished_at IS NOT NULL, 1, 0) as finished';

    private $columnsJoinedPPL= 'userParticipations.*, 
    if(ppl.started_at IS NOT NULL, 1, 0) as started, 
    if(ppl.finished_at IS NOT NULL, 1, 0) as finished';

    function create(int $userId, array $columns, array $valueIds){
        $data = array(
			"user_id" => $userId,
			"joined_at" => $this->db->now(),
	    );
        foreach($columns as $ind => $col){
            $data[$col] = $valueIds[$ind];
        }
        if(!$this->db->insert($this->tableName, $data)){
            throw new \App\Exception\Mysql($this->db->getLastError(), 500);
        }
        return true;
    }

    //TODO change string type to ENUM 'ppLeague_id', 'ppCupGroup_id'
    function getUserParticipations(int $userId, ?string $type, ?bool $active, ?int $minPosition){
        $this->db->where('user_id', $userId);

        if($active){
            $this->db->having('finished', 0);
            $this->db->orderBy('started','desc');
        }
        if($minPosition){
            $this->db->where('position', $minPosition, '<=');
        }
        if($type){
            $this->db->where($type.' IS NOT NULL');
        }
        // $this->db->orderBy('joined_at','desc');

        $this->db->join('ppLeagues ppl', 'ppl.id = userParticipations.ppLeague_id', "LEFT");
        $this->db->join('ppCupGroups ppcg', 'ppcg.id = userParticipations.ppCupGroup_id', "LEFT");
        
        return $this->db->get($this->tableName, null, $this->columnsJoined3) ;
                
    }

    function getForTournament(string $tournamentColumn, int $tournamentId){
        $this->db->join("users u", "u.id=up.user_id", "INNER");
        $this->db->orderBy('up.position','asc');
        $this->db->where($tournamentColumn, $tournamentId);
        return $this->db->query("SELECT up.*, u.username FROM userParticipations up");
    }
    


    function getPromotedTournamentTypesForUser(int $userId, bool $include_ppCups = false, bool $return_id_only = true){
        $this->db->where('user_id',$userId);
        $this->db->having('finished',1);
        $this->db->where('position', $_SERVER['PPLEAGUE_TROPHY_POSITION'], "<=");
        if(!$include_ppCups) $this->db->where('ppLeague_id IS NOT NULL');

        $this->db->join('ppLeagues ppl', 'ppl.id = userParticipations.ppLeague_id', "INNER");
        $result = $this->db->get($this->tableName, null, $this->columnsJoinedPPL) ;

        return $return_id_only ? array_column($result, 'ppTournamentType_id') : $result;        
        
    }

    function getCurrentTournamentTypesForUser(int $userId, bool $include_ppCups = false, bool $return_id_only = true){
        $this->db->where('user_id', $userId);
        
        $this->db->having('finished',0);
        $this->db->join('ppLeagues ppl', 'ppl.id = userParticipations.ppLeague_id', "LEFT");
        $this->db->join('ppCupGroups ppcg', 'ppcg.id = userParticipations.ppCupGroup_id', "LEFT");

        if(!$include_ppCups) $this->db->where('ppLeague_id IS NOT NULL');

        $result = $this->db->get($this->tableName, null, $this->columnsJoined3) ;
        return $return_id_only ? array_column($result, 'ppTournamentType_id') : $result; 

    }

    function getOverallPPCupPoints(int $userId, int $cupId, ?string $joinedBefore) : ?int{
        $this->db->where('user_id',$userId);
        $this->db->where('ppCup_id',$cupId);
        if($joinedBefore)$this->db->where('joined_at', $joinedBefore, '<');
        return (int)$this->db->getOne($this->tableName, 'sum(tot_points) as points_total')['points_total'];
    }

    
    function update(int $id, ?int $tot_points, ?int $tot_unox2, ?int $tot_locked, ?int $tot_preso, ?int $position, ?int $tot_cup_points = null){
        $data = array(
			"tot_points" => $tot_points,
			"tot_locked" => $tot_locked,
			"tot_preso" => $tot_preso,
			"tot_unox2" => $tot_unox2,
			"tot_cup_points" => $tot_cup_points,
            "position" => $position,
            "updated_at" => $this->db->now(),
		);
        $this->db->where('id',$id);
        $this->db->update($this->tableName, $data, 1);
    }

    public function setFinished(string $tournamentColumn, int $tournamentId){
        $data = array(
			"finished" => 1,
            "updated_at" => $this->db->now(),
		);
        $this->db->where($tournamentColumn, $tournamentId);
        $this->db->update($this->tableName, $data);
    }

    public function setStarted(string $tournamentColumn, int $tournamentId){
        $data = array(
			"tot_points" => 0,
            "updated_at" => $this->db->now(),
		);
        $this->db->where($tournamentColumn, $tournamentId);
        $this->db->update($this->tableName, $data);
    }

    public function count(string $tournamentColumn, int $tournamentId){
        $this->db->where($tournamentColumn, $tournamentId);
        return $this->db->getValue($this->tableName, "COUNT(DISTINCT(user_id))");
    }

    public function isUserInTournament(int $userId, string $tournamentColumn, int $tournamentId){
        $this->db->where($tournamentColumn, $tournamentId);
        $this->db->where('user_id', $userId);
        return $this->db->has($this->tableName);
    }

    public function isUserInTournamentType(int $userId, int $ppTournamentType_id){
        $this->db->where($this->tableName.'.ppTournamentType_id',$ppTournamentType_id);
        $this->db->where('user_id', $userId);
        
        $this->db->having('finished', 0);
        $this->db->join('ppLeagues ppl', 'ppl.id = userParticipations.ppLeague_id', "LEFT");
        $this->db->join('ppCupGroups ppcg', 'ppcg.id = userParticipations.ppCupGroup_id', "LEFT");
        
        return !!$this->db->getOne($this->tableName, $this->columnsJoined3) ;

        // return $this->db->has($this->tableName);
    }
}