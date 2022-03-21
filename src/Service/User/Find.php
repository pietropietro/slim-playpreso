<?php

declare(strict_types=1);

namespace App\Service\User;

final class Find extends Base
{
    public function getOne(int $userId) 
    {
        if (self::isRedisEnabled() === true && $cached = $this->getUserFromCache($userId)) {
            return $cached;
        } 
        
        $user = $this->getUserFromDb($userId);
        $guesses = $this->guessRepository->getUserGuesses($userId);

        $guessesWithMatch = array();
        foreach ($guesses as $guess) {
            $match = $this->matchRepository->getMatch($guess['match_id']);
            $guess['match'] = $match;
            array_push($guessesWithMatch, $guess);
        }
        $user['guesses'] = $guessesWithMatch;

        //TODO update w/ new db schema
        // $userPPLeagueIds =  $this->userInPPLeaguesRepository->getUserPPLeagueIds($userId, true);
        // $user['ppLeagues'] = $this->ppLeagueRepository->getPPLeagues($userPPLeagueIds);

        //TODO  placements merged into userPArticipations
        $user['trophies'] = $this->UserParticipationRepository->getPlacements($userId);
        
        if (self::isRedisEnabled() === true){
            $this->saveInCache($userId, (object) $user);
        }


        
        return $user;
    }

    public function getAvailablePPLeagueTypes(int $userId) 
    {
        if (self::isRedisEnabled() === true && $cached = $this->getAvailablePPLeagueTypesFromCache($userId)) {
            return $cached;
        } 

        //1. get qualified ppLT ids
        $OkPPLeagueTypeIds = $this->UserParticipationRepository->getOkPPLeagueTypeIdsForUser($userId);
        //2. get those ppLTs + 1 level  OR  same level if max, and level 1 for others
        $availablePPLeagueTypes = $this->ppLeagueTypeRepository->getHigherPPLeagueTypes($OkPPLeagueTypeIds);

        return $availablePPLeagueTypes;        
    }

}
