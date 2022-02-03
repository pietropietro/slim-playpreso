<?php

declare(strict_types=1);

namespace App\Service\User;

final class Find extends Base
{
    //TODO add OO : User
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

        $userPresoLeagueIds =  $this->userInPresoLeaguesRepository->getUserPresoLeagueIds($userId, true);
        $user['presoLeagues'] = $this->presoLeagueRepository->getPresoLeagues($userPresoLeagueIds);

        $user['trophies'] = $this->trophyRepository->getTrophies($userId);
        
        $this->saveInCache($userId, (object) $user);

        // if($user['guesses']){
        //     // $user['userTopStats'] = getTopStatsForUser($userId);
        //     $user['leagueTopStats'] = getTopStats($userId);
        //     $user['average'] = calculateUserAverage($userId,20);
        // }
        

        return $user;
    }
}
