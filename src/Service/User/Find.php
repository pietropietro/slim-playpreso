<?php

declare(strict_types=1);

namespace App\Service\User;

final class Find extends Base
{
    public function getOne(int $userId) : obj
    {
        if (self::isRedisEnabled() === true) {
            $user = $this->getUserFromCache($userId);
        } else {
            $user = $this->getUserFromDb($userId);

            $user['guesses'] = $this->getGuessesForUser($userId);
            
            $userPresoLeagueIds =  $this->getUserPresoLeagueIds($userId);
            $user['presoLeagues'] = $this->getPresoLeagues($userPresoLeagueIds);

            $user['trophies'] = $this->getTrophies($userId);

            // if($user['guesses']){
            //     // $user['userTopStats'] = getTopStatsForUser($userId);
            //     $user['leagueTopStats'] = getTopStats($userId);
            //     $user['average'] = calculateUserAverage($userId,20);
            // }
        }

        return $user;
    }
}
