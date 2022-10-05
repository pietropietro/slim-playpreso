<?php

declare(strict_types=1);

namespace App\Repository;


final class PPTournamentTypeRepository extends BaseRepository
{
    //returns an array like (0: [name: 'america', maxLevel: 2, ppTTids: '21,23'])
    function getPPLeaguesMap(){
        return $this->db->query('SELECT name, max(level) as maxLevel, 
            GROUP_CONCAT(id) as ppTTids
            FROM ppTournamentTypes where cup_format IS NULL GROUP BY name ORDER BY maxLevel ');
    }

    function get(array $ids){
        $this->db->where('id',$ids,'IN');
        return $this->db->get('ppTournamentTypes');
    }

    function getOne(int $id){
        $this->db->where('id',$id);
        return $this->db->getOne('ppTournamentTypes');
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

}