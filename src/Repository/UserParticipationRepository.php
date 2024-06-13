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

    public function getOne(int $userId,  string $tournamentColumn, int $tournamentId){
        $this->db->where('user_id', $userId);
        $this->db->where('userParticipations.'.$tournamentColumn, $tournamentId);
        $this->db->join('ppLeagues ppl', 'ppl.id = userParticipations.ppLeague_id', "LEFT");
        $this->db->join('ppCupGroups ppcg', 'ppcg.id = userParticipations.ppCupGroup_id', "LEFT");
        return $this->db->getOne('userParticipations', $this->columnsJoined3);
    }

    public function get(array $ids){
        $this->db->where($this->tableName.'.id', $ids, 'IN');
        $this->db->join('ppLeagues ppl', 'ppl.id = userParticipations.ppLeague_id', "LEFT");
        $this->db->join('ppCupGroups ppcg', 'ppcg.id = userParticipations.ppCupGroup_id', "LEFT");
        return $this->db->get($this->tableName, null, $this->columnsJoined3);
    }

    //TODO change type to ENUM 'ppLeague_id', 'ppCupGroup_id'
    public function getForUser(
        int $userId, 
        ?string $type = null, 
        ?bool $started = null, 
        ?bool $finished = null, 
        ?int $minPosition = null,
        ?string $updatedAfter = null,
        ?string $updatedBefore = null
    ){
        $this->db->where('user_id', $userId);

        if(isset($started)){
            $this->db->having('started', $started);
            // $this->db->orderBy('started','desc');
        }
        if(isset($finished)){
            $this->db->having('finished', $finished);
            // $this->db->orderBy('finished','desc');
        }
        if($minPosition){
            $this->db->where('position', $minPosition, '<=');
            $this->db->where('position is not null');
        }
        if($type){
            $this->db->where('userParticipations.'.$type.' IS NOT NULL');
        }
        if($updatedAfter){
            $dateAfter = date("Y-m-d H:i:s", strtotime($updatedAfter));
            $this->db->where('updated_at', $dateAfter, '>');
        }
        if($updatedBefore){
            $dateBefore = date("Y-m-d H:i:s", strtotime($updatedBefore));
            $this->db->where('updated_at', $dateBefore, '<');
        }
        // $this->db->orderBy('joined_at','desc');

        $this->db->join('ppLeagues ppl', 'ppl.id = userParticipations.ppLeague_id', "LEFT");
        $this->db->join('ppCupGroups ppcg', 'ppcg.id = userParticipations.ppCupGroup_id', "LEFT");
        
        return $this->db->get($this->tableName, null, $this->columnsJoined3);
    }

    function getForTournament(
        string $tournamentColumn, 
        int $tournamentId, 
        ?int $level=null, 
        ?int $position=null,
        ?int $limit=null,
        ?bool $orderByPoints=null
    ){
        $this->db->join("users u", "u.id=userParticipations.user_id", "INNER");
        $this->db->join('ppLeagues ppl', 'ppl.id = userParticipations.ppLeague_id', "LEFT");
        $this->db->join('ppCupGroups ppcg', 'ppcg.id = userParticipations.ppCupGroup_id', "LEFT");
        $this->db->orderBy('userParticipations.position','asc');
        $this->db->where('userParticipations.'.$tournamentColumn, $tournamentId);
        if($level)$this->db->where('level', $level);
        if($position) $this->db->where('position',$position);
        if($orderByPoints) $this->db->orderBy('tot_points', 'desc');
        return $this->db->get('userParticipations', $limit, $this->columnsJoined3.',u.username');
    }

    public function getCupWins(int $userId){
        if(!$userId)return;
        return $this->db->query('
            SELECT up.* 
            FROM userParticipations up 
            JOIN ppCupGroups pcg ON up.ppCupGroup_id = pcg.id 
            JOIN ppCups pc ON pcg.ppCup_id = pc.id 
            WHERE up.user_id = '.$userId.'
            AND up.position = 1 
            AND pcg.level = (SELECT MAX(level) FROM ppCupGroups WHERE ppCup_id = pc.id)
        ');
    }
    


    function getPromotedTournamentTypesForUser(int $userId, bool $include_ppCups = false, bool $return_id_only = true){
        $this->db->where('user_id',$userId);
        $this->db->having('finished',1);
        $this->db->where('position', $_SERVER['PPLEAGUE_PROMOTIONS'], "<=");
        $this->db->where('EBR is null');
        if(!$include_ppCups) $this->db->where('ppLeague_id IS NOT NULL');

        $this->db->join('ppLeagues ppl', 'ppl.id = userParticipations.ppLeague_id', "INNER");
        $result = $this->db->get($this->tableName, null, $this->columnsJoinedPPL) ;

        return $return_id_only ? array_column($result, 'ppTournamentType_id') : $result;        
        
    }

    function getCurrentTournamentTypesForUser(int $userId, bool $include_ppCups = false){
        $this->db->where('user_id', $userId);
        
        $this->db->having('finished',0);
        $this->db->join('ppLeagues ppl', 'ppl.id = userParticipations.ppLeague_id', "LEFT");
        $this->db->join('ppTournamentTypes pptt', 'pptt.id = userParticipations.ppTournamentType_id', "INNER");
        $this->db->join('ppCupGroups ppcg', 'ppcg.id = userParticipations.ppCupGroup_id', "LEFT");
        
        if(!$include_ppCups){
            $this->db->where('ppLeague_id IS NOT NULL');
        }

        $result = $this->db->get(
            $this->tableName, 
            null, 
            'pptt.*, if(ppl.finished_at IS NOT NULL or ppcg.finished_at IS NOT NULL, 1, 0) as finished'
        );
        return $result; 
    }

    function getOverallPPCupPoints(int $userId, int $cupId, ?string $joinedBefore) : ?int{
        $this->db->where('user_id',$userId);
        $this->db->where('ppCup_id',$cupId);
        if($joinedBefore)$this->db->where('joined_at', $joinedBefore, '<');
        return (int)$this->db->getOne($this->tableName, 'sum(tot_points) as points_total')['points_total'];
    }

    
    function update(
        int $id, 
        ?int $tot_points, 
        ?int $tot_unox2, 
        ?int $tot_locked, 
        ?int $tot_preso, 
        ?int $tot_uo25, 
        ?int $tot_ggng, 
        ?int $tot_score_diff, 
        ?int $position, 
        ?int $tot_cup_points = null,
    ){
        $data = array(
			"tot_points" => $tot_points,
			"tot_locked" => $tot_locked,
			"tot_preso" => $tot_preso,
			"tot_unox2" => $tot_unox2,
			"tot_uo25" => $tot_uo25,
			"tot_ggng" => $tot_ggng,
			"tot_score_diff" => $tot_score_diff,
			"tot_cup_points" => $tot_cup_points,
            "position" => $position,
            "updated_at" => $this->db->now(),
		);
        $this->db->where('id',$id);
        $this->db->update($this->tableName, $data, 1);
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

    public function setEBR(int $user_id, int $byPPLeague_id , int $ppTournamentType_id){
        $data = array(
			"EBR" => $byPPLeague_id
		);
        $this->db->where('user_id', $user_id);
        $this->db->where('ppTournamentType_id', $ppTournamentType_id);
        $this->db->where('position', $_SERVER['PPLEAGUE_PROMOTIONS'], '<=');

        return $this->db->update('userParticipations', $data, 1);
    }

    //this function returns ups which ended up in position next/before to the original up
    public function findAdjacentParticipants(int $userId, array $participation){
        // Prepare the query for adjacent positions
        $this->db->join('users u', 'up2.user_id=u.id', 'INNER');
        $this->db->where('up2.user_id', $userId, '!=');
        $this->db->where('up2.position', [$participation['position'] - 1, $participation['position'] + 1], 'IN');
        
        if ($participation['ppLeague_id'] !== null) {
            $this->db->where('up2.ppLeague_id', $participation['ppLeague_id']);
        } else if ($participation['ppCupGroup_id'] !== null) {
            $this->db->where('up2.ppCupGroup_id', $participation['ppCupGroup_id']);
        }

        $adjacentParticipations = $this->db->get('userParticipations up2', null, 'up2.user_id, username, up2.position, tot_points');
        
        return $adjacentParticipations;
    }

    
    public function getUserSchemaPPLeagues(int $userId): array
    {
        // Subquery for leagues
        $subQueryLeagues = "
            SELECT 
                up.*,
                pl.started_at AS up_started_at,
                pl.finished_at AS up_finished_at,
                ROW_NUMBER() OVER (PARTITION BY up.ppTournamentType_id ORDER BY up.tot_points DESC, up.position ASC) AS rn
            FROM 
                userParticipations up
            JOIN 
                ppLeagues pl ON up.ppLeague_id = pl.id
            WHERE 
                up.user_id = $userId
                AND up.ppLeague_id IS NOT NULL
        ";

        // Main query
        $this->db->join("($subQueryLeagues) up_leagues", "pptt.id = up_leagues.ppTournamentType_id AND up_leagues.rn = 1", "LEFT");

        // Add where condition and order by clauses
        $this->db->where('pptt.cup_format', null, 'IS');
        $this->db->where('pptt.name', 'MOTD', '!=');
        $this->db->orderBy('pptt.name', 'ASC');
        $this->db->orderBy('pptt.level', 'ASC');

        // Define the columns to retrieve
        $columns = [
            'pptt.id AS pptt_id',
            'pptt.name AS pptt_name',
            'pptt.level AS pptt_level',
            'pptt.emoji AS pptt_emoji',
            'up_leagues.user_id AS up_user_id',
            'up_leagues.id AS up_id',
            'up_leagues.ppLeague_id AS up_ppLeague_id',
            'up_leagues.updated_at AS up_updated_at',
            'up_leagues.tot_points AS up_tot_points',
            'up_leagues.position AS up_position',
            'up_leagues.up_started_at',
            'up_leagues.up_finished_at'
        ];

        // Execute the query
        return $this->db->get('ppTournamentTypes pptt', null, $columns);
    }




    public function getUserSchemaPPCups(int $userId): array
    {
        // Subquery for cups
        $subQueryCups = "
            SELECT 
                up.*,
                ppcg.level AS cup_level,
                pl.started_at AS pl_started_at,
                pl.finished_at AS pl_finished_at,
                ROW_NUMBER() OVER (PARTITION BY up.ppTournamentType_id ORDER BY ppcg.level DESC, up.position ASC) AS rn
            FROM 
                userParticipations up
            JOIN 
                ppCupGroups ppcg ON up.ppCupGroup_id = ppcg.id
            JOIN 
                ppCups pl ON up.ppCup_id = pl.id
            WHERE 
                up.user_id = $userId
                AND up.ppCup_id IS NOT NULL
        ";

        // Main query
        $this->db->join("($subQueryCups) up_cups", "pptt.id = up_cups.ppTournamentType_id AND up_cups.rn = 1", "LEFT");

        // Add where condition and order by clauses
        $this->db->where('pptt.cup_format', null, 'IS NOT');
        $this->db->where('pptt.name', 'MOTD', '!=');
        $this->db->orderBy('pptt.name', 'ASC');

        // Define the columns to retrieve
        $columns = [
            'pptt.id AS pptt_id',
            'pptt.name AS pptt_name',
            'pptt.level AS pptt_level',
            'pptt.emoji AS pptt_emoji',
            'pptt.cup_format AS pptt_cup_format',
            'up_cups.user_id AS up_user_id',
            'up_cups.id AS up_id',
            'up_cups.ppCup_id AS up_ppCup_id',
            'up_cups.updated_at AS up_updated_at',
            'up_cups.tot_points AS up_tot_points',
            'up_cups.position AS up_position',
            'up_cups.pl_started_at AS up_started_at',
            'up_cups.pl_finished_at AS up_finished_at',
            'up_cups.cup_level AS up_cup_level'
        ];

        // Execute the query
        return $this->db->get('ppTournamentTypes pptt', null, $columns);
    }




}