<?php

declare(strict_types=1);

namespace App\Repository;


final class PPLeagueTypeRepository extends BaseRepository
{

    function getBasePPLTypes(){
        $this->getDb()->where('level',1);
        return $this->getDb()->get('ppLeagueTypes');
    }

    function getPPLTypesMap(){
        //TODO redis this
        return $this->getDb()->query('SELECT type, max(level) as maxLevel, GROUP_CONCAT(id) ppLTIds 
        FROM ppLeagueTypes GROUP BY type ORDER BY maxLevel ');
    }

    // function getNext
	// if($PLtypes = $db->get('PLtypes')){
	// 	$nextLevels = array();
	// 	foreach ($PLtypes as $PLT) {
	// 		if($nextLevel = getNextPLLevel($PLT['id'])){
	// 			array_push($nextLevels, $nextLevel);
	// 		}	
	// 	}	
	// 	return $nextLevels;
	// }
// }

}