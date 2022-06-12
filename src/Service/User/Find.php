<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Repository\UserRepository;
use App\Service\RedisService;

final class Find extends Base
{
    public function __construct(
        protected UserRepository $userRepository,
        protected RedisService $redisService
    ) {
    }

    public function getOne(int $userId) 
    {
        if (self::isRedisEnabled() === true && $cached = $this->getUserFromCache($userId)) {
            return $cached;
        } 
        
        $user = $this->getUserFromDb($userId);

        // $guesses = $this->guessRepository->getUserGuesses($userId);
        // $guessesWithMatch = array();
        // foreach ($guesses as $guess) {
        //     $match = $this->matchRepository->getOne($guess['match_id']);
        //     $guess['match'] = $match;
        //     array_push($guessesWithMatch, $guess);
        // }
        // $user['guesses'] = $guessesWithMatch;

        // $activePPLeagueIds =  $this->userParticipationRepository->getUserPPLeagueIds($userId, true);
        // $user['ppLeagues'] = $this->ppLeagueRepository->getPPLeagues($activePPLeagueIds);

        // $user['participations'] = $this->userParticipationRepository->getParticipations($userId);
        
        if (self::isRedisEnabled() === true){
            $this->saveInCache($userId, (object) $user);
        }

        return $user;
    }

}
