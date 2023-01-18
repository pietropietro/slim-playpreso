<?php

declare(strict_types=1);

namespace App\Repository;


final class PPTournamentTypeRepository extends BaseRepository
{
    function get(?array $ids, ?bool $onlyCups){
        if($ids)$this->db->where('id',$ids,'IN');
        if($onlyCups)$this->db->where('cup_format IS NOT NULL');
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

    function availablePPLeaguesTypes(array $excludeNames, array $excldueIds, int $userPoints){
        
        $textQuery = "
            select * from ppTournamentTypes pptt 
            where pptt.level = 
                (select min(level) from ppTournamentTypes pptt2 
                    where pptt2.name = pptt.name 
                    and cup_format is null and cost < " . $userPoints;

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


    function filterIdsExpensive(array $ids, int $points){
        $this->db->where('id', $ids, 'IN');
        $this->db->where('cost', $points, '<=');
        return $this->db->getValue('ppTournamentTypes', 'id', null);
    }

    public function update(int $id, array $data){
        $this->db->where('id', $id);
        return $this->db->update('ppTournamentTypes', $data, 1);        
    }

}