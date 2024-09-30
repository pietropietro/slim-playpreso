<?php

declare(strict_types=1);

namespace App\Repository;


final class PPTournamentTypeRepository extends BaseRepository
{

    public function create(
        string $name, 
        int $cost, 
        string $rgb,
        string $emoji,
        ?int $level = null, 
        ?int $rounds = null, 
        ?int $participants = null,
        ?string $pick_country = null,
        ?int $pick_area = null,
        ?int $pick_league = null,
    ){
        $data = array(
            "name" => $name, 
            "cost" => $cost, 
            "rgb" => $rgb,
            "emoji" => $emoji,
            "level" => $level, 
            "rounds" => $rounds, 
            "participants" => $participants,
            "pick_country" => $pick_country,
            "pick_area" => $pick_area,
            "pick_league" => $pick_league,
        );
        return $this->db->insert('ppTournamentTypes', $data);
    }


    function get(?array $ids, ?bool $onlyCups = false, ?bool $onlyLeagues = false){
        if($ids)$this->db->where('id',$ids,'IN');
        if($onlyCups)$this->db->where('cup_format IS NOT NULL');
        else if($onlyLeagues)$this->db->where('cup_format IS NULL');
        $this->db->orderBy('name', 'asc');
        $this->db->orderBy('level', 'asc');
        return $this->db->get('ppTournamentTypes');
    }

    function getOne(int $id){
        $this->db->where('id',$id);
        return $this->db->getOne('ppTournamentTypes');
    }

    public function getOneFromPPTournament(string $tournamentTable, int $tournamentId){
        $this->db->join( $tournamentTable.' ppt', 'ppTournamentTypes.id = ppt.ppTournamentType_id');
        $this->db->where('ppt.id', $tournamentId);
        return $this->db->getOne('ppTournamentTypes', "ppTournamentTypes.*");
    }

    function getByNameAndLevel(string $name, int $level){
        $this->db->where('name',$name);
        $this->db->where('level',$level);
        return $this->db->getOne('ppTournamentTypes');
    }


    function getCost(int $id){
        $this->db->where('id',$id);
        return $this->db->getOne('ppTournamentTypes', 'cost');
    }

    function availablePPLeaguesTypes(array $excludeNames, array $excludeIds, int $userPoints){
        
        $textQuery = "SELECT * from ppTournamentTypes pptt 
            where pptt.level = 
                (select min(level) from ppTournamentTypes pptt2 
                    where pptt2.name = pptt.name 
                    and cup_format is null and cost <= " . $userPoints;

        if($excludeNames){
            $textQuery .= ' and name not in ("'.implode('", "' ,$excludeNames).'")';
        }
        if($excludeIds){
            $textQuery .= " and id not in (".implode(',',$excludeIds).")";
        }
        $textQuery .= ")";      

        $result = $this->db->query($textQuery);
        return $result;
    }

    function availablePPCupsForUser(int $userId){
        $activeCupIds = $this->db->subQuery();
        $activeCupIds->join("ppTournamentTypes pptt", "userParticipations.ppTournamentType_id = pptt.id", "INNER");
        $activeCupIds->where('userParticipations.user_id', $userId);
        $activeCupIds->where('userParticipations.finished', 0);
        $activeCupIds->where('pptt.cup_format IS NOT NULL');
        $activeCupIds->get('userParticipations', null, 'ppTournamentType_id');

        $this->db->where('id', $activeCupIds, 'NOT IN');
        $this->db->where('cup_format IS NOT NULL');
        $this->db->where('can_join', 1);
        return $this->db->get("ppTournamentTypes");
    }

    public function update(int $id, array $data){
        $this->db->where('id', $id);
        return $this->db->update('ppTournamentTypes', $data, 1);        
    }

    public function getHavingParticipants(array $ids){
        if(!$ids)return null;
        // this does not work because of the having clause
        // need to do the raw query instead
        // $this->db->join('ppTournamentTypes pptt', 'up.ppTournamentType_id = pptt.id', 'INNER');
        // $this->db->join('ppLeagues ppl', 'up.ppLeague_id = ppl.id ', 'INNER');
        // $this->db->where('up.ppLeague_id is not null');
        // $this->db->where('ppl.started_at is null');
        // // $this->db->where('ppl.started_at is null');
        // $this->db->groupBy('up.ppLeague_id, up.ppTournamentType_id');
        // $this->db->having('cnt', 'pptt.participants', '<');
        // $this->db->orderBy('cnt');
        // $result = $this->db->get('userParticipations up',null,'pptt.*, up.ppLeague_id, up.ppTournamentType_id, COUNT(*) as cnt');
        // return $result;
        
        $sql = "SELECT pptt.*, up.ppLeague_id, up.ppTournamentType_id, COUNT(*) as user_cnt
            FROM userParticipations up
            inner join ppTournamentTypes pptt on up.ppTournamentType_id = pptt.id
            inner join ppLeagues ppl on up.ppLeague_id = ppl.id 
            where ppl.started_at is null
            and up.ppLeague_id is NOT null
            and up.ppTournamentType_id in (".implode(',', $ids).")
            GROUP BY up.ppLeague_id, up.ppTournamentType_id
            having user_cnt < pptt.participants
            order by user_cnt desc";
        return $this->db->query($sql);
    }

    public function getFromPPRoundMatch(int $ppRoundMatchId) {
        $sql = "SELECT 
                    ppTournamentTypes.id,
                    ppTournamentTypes.name,
                    ppTournamentTypes.level,
                    ppTournamentTypes.emoji,
                    ppTournamentTypes.rgb,
                    tournament_id,
                    CASE 
                        WHEN ppTournamentTypes.cup_format IS NOT NULL THEN 1 
                        ELSE 0 
                    END AS is_cup
                FROM ppTournamentTypes 
                INNER JOIN 
                    (SELECT 
                        IF(ppLeague_id, ppLeague_id, ppCupGroup_id) AS tournament_id, 
                        IF(ppl.ppTournamentType_id, ppl.ppTournamentType_id, ppcg.ppTournamentType_id) AS ppTournamentType_id
                     FROM ppRounds ppr 
                     LEFT JOIN ppLeagues ppl ON ppl.id = ppr.ppLeague_id  
                     LEFT JOIN ppCupGroups ppcg ON ppcg.id = ppr.ppCupGroup_id
                     WHERE ppr.id = (SELECT ppRound_id FROM ppRoundMatches WHERE id = ".$ppRoundMatchId.")) aggr
                ON aggr.ppTournamentType_id = ppTournamentTypes.id";
        $result = $this->db->query($sql);
        return $result[0] ?? null;
    }

    public function getFromPPRound(int $ppRoundId) {
        $sql = "
            SELECT 
                ppTournamentTypes.id,
                ppTournamentTypes.name,
                ppTournamentTypes.level,
                ppTournamentTypes.emoji,
                ppTournamentTypes.rgb,
                CASE 
                    WHEN ppTournamentTypes.cup_format IS NOT NULL THEN 1 
                    ELSE 0 
                END AS is_cup
            FROM ppTournamentTypes 
            LEFT JOIN ppLeagues ppl ON ppTournamentTypes.id = ppl.ppTournamentType_id
            LEFT JOIN ppCupGroups ppcg ON ppTournamentTypes.id = ppcg.ppTournamentType_id
            WHERE ppl.id = (SELECT ppLeague_id FROM ppRounds WHERE id = ?) 
            OR ppcg.id = (SELECT ppCupGroup_id FROM ppRounds WHERE id = ?)
        ";
    
        // Execute the query and pass the ppRoundId twice (for ppLeague and ppCupGroup)
        $result = $this->db->rawQuery($sql, [$ppRoundId, $ppRoundId]);
        
        return $result[0] ?? null;
    }
    
    
    public function getMOTDType(){
        $this->db->where('name', 'MOTD');
        return $this->db->getOne('ppTournamentTypes');
    }

    public function getUps(int $id, ?int $userId, ?int $limit=1){
        $this->db->join('users u', 'up.user_id = u.id');
        $this->db->where('ppTournamentType_id', $id);
        $this->db->where('tot_points is not null');
        $this->db->orderBy('tot_points');
        if($userId)$this->db->where('user_id', $userId);
        $columns = 'user_id, username, ppLeague_id, tot_points, tot_locked, tot_preso, tot_unox2, updated_at';
        return $this->db->get(
            'userParticipations up', $limit, $columns
        );
    }    

}
