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
    ){
        $data = array(
            "name" => $name, 
            "cost" => $cost, 
            "rgb" => $rgb,
            "emoji" => $emoji,
            "level" => $level, 
            "rounds" => $rounds, 
            "participants" => $participants
        );
        return $this->db->insert('ppTournamentTypes', $data);
    }


    function get(?array $ids, ?bool $onlyCups = false){
        if($ids)$this->db->where('id',$ids,'IN');
        if($onlyCups)$this->db->where('cup_format IS NOT NULL');
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

    public function getCloseToStart(array $ids){
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
            and ppl.started_at is null
            and up.ppLeague_id is NOT null
            and up.ppTournamentType_id in (".implode(',', $ids).")
            GROUP BY up.ppLeague_id, up.ppTournamentType_id
            having user_cnt < pptt.participants
            order by user_cnt desc";
        return $this->db->query($sql);
    }
}
