<?php

declare(strict_types=1);

namespace App\Repository;

final class PPLeagueTypeRepository extends BaseRepository
{

    function getHigherPPLeagueTypes($ppLTIds){
        // if(!$ppLTIds){
            return $this->getBasePPLTypes();
        // }

        $ppLTypes = $this->getDb()->get('ppLeagueTypes');
        //TODO 
        //divide by type (America, Europe ...)
        //get next level from given ids
        //or same level
        //or base level 
        
        return array_column($ppLTIds,'ppLeagueType_id');
    }

    function getBasePPLTypes(){
        $this->getDb()->where('level',1);
        return $this->getDb()->get('ppLeagueTypes');
    }

}