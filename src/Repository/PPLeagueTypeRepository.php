<?php

declare(strict_types=1);

namespace App\Repository;


final class PPLeagueTypeRepository extends BaseRepository
{

    function getBasePPLTypes(){
        $this->db->where('level',1);
        return $this->db->get('ppLeagueTypes');
    }

    function getMap(){
        return $this->db->query('SELECT type, max(level) as maxLevel, 
            GROUP_CONCAT(id) ppLTIds
            FROM ppLeagueTypes GROUP BY type ORDER BY maxLevel ');
    }

    function get(array $ids){
        $this->db->where('id',$ids,'IN');
        return $this->db->get('ppLeagueTypes');
    }

    function getOne(int $id){
        $this->db->where('id',$id);
        return $this->db->getOne('ppLeagueTypes');
    }

    function getCost(int $id){
        $this->db->where('id',$id);
        return $this->db->getOne('ppLeagueTypes', 'cost');
    }


    function filterIdsExpensive(array $ids, int $points){
        $this->db->where('id', $ids, 'IN');
        $this->db->where('cost', $points, '<=');
        return $this->db->getValue('ppLeagueTypes', 'id', null);

    }

}