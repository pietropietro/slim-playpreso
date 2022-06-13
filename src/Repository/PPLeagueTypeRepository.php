<?php

declare(strict_types=1);

namespace App\Repository;


final class PPLeagueTypeRepository extends BaseRepository
{

    function getBasePPLTypes(){
        $this->getDb()->where('level',1);
        return $this->getDb()->get('ppLeagueTypes');
    }

    function getMap(){
        return $this->getDb()->query('SELECT type, max(level) as maxLevel, 
            GROUP_CONCAT(id) ppLTIds
            FROM ppLeagueTypes GROUP BY type ORDER BY maxLevel ');
    }

    function get($ids){
        $this->getDb()->where('id',$ids,'IN');
        return $this->getDb()->get('ppLeagueTypes');
    }

    function getOne($id){
        $this->getDb()->where('id',$id);
        return $this->getDb()->getOne('ppLeagueTypes');
    }

    function getCost($id){
        $this->getDb()->where('id',$id);
        return $this->getDb()->getOne('ppLeagueTypes', 'cost');
    }


    function filterIdsExpensive($ids, $points){
        $this->getDb()->where('id', $ids, 'IN');
        $this->getDb()->where('cost', $points, '<=');
        return $this->getDb()->getValue('ppLeagueTypes', 'id', null);

    }

}