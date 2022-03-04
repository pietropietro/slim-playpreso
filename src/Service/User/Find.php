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

        echo("retrieved guesses : ");
        print_r($guesses);

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

        $user['trophies'] = $this->userPlacementsRepository->getPlacements($userId);
        
        if (self::isRedisEnabled() === true){
            $this->saveInCache($userId, (object) $user);
        }

        // if($user['guesses']){
        //     // $user['userTopStats'] = getTopStatsForUser($userId);
        //     $user['leagueTopStats'] = getTopStats($userId);
        //     $user['average'] = calculateUserAverage($userId,20);
        // }
        

        return $user;
    }
}
