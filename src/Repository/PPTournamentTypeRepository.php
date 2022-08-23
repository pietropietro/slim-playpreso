<?php

declare(strict_types=1);

namespace App\Repository;


final class PPTournamentTypeRepository extends BaseRepository
{

    function getPPLeaguesMap(){
        return $this->db->query('SELECT type, max(level) as maxLevel, 
            GROUP_CONCAT(id) ppLTIds where is_ppCup IS FALSE
            FROM ppTournamentTypes GROUP BY type ORDER BY maxLevel ');
    }

    function get(array $ids){
        $this->db->where('id',$ids,'IN');
        return $this->db->get('ppTournamentTypes');
    }

    function getOne(int $id){
        $this->db->where('id',$id);
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