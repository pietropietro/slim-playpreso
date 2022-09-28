<?php

declare(strict_types=1);

namespace App\Repository;


final class PPTournamentTypeRepository extends BaseRepository
{
    //returns an array like (0: [name: 'america', maxLevel: 2, ppTTids: '21,23'])
    function getPPLeaguesMap(){
        return $this->db->query('SELECT name, max(level) as maxLevel, 
            GROUP_CONCAT(id) as ppTTids
            FROM ppTournamentTypes where is_ppCup = false GROUP BY name ORDER BY maxLevel ');
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


    function filterIdsExpensive(array $ids, int $points){
        $this->db->where('id', $ids, 'IN');
        $this->db->where('cost', $points, '<=');
        return $this->db->getValue('ppTournamentTypes', 'id', null);

    }

}