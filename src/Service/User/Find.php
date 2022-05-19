<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Repository\UserRepository;
use App\Service\RedisService;
use App\Repository\PPLeagueRepository;
use App\Repository\GuessRepository;
use App\Repository\MatchRepository;
use App\Repository\UserParticipationRepository;
use App\Repository\PPLeagueTypeRepository;

final class Find extends Base
{
    public function __construct(
        protected UserRepository $userRepository,
        protected RedisService $redisService,
        protected UserParticipationRepository $userParticipationRepository,
        protected GuessRepository $guessRepository,
        protected MatchRepository $matchRepository,
        protected PPLeagueTypeRepository $ppLeagueTypeRepository,
        protected PPLeagueRepository $ppLeagueRepository,
    ) {
    }

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

        $activePPLeagueIds =  $this->userParticipationRepository->getUserPPLeagueIds($userId, true);
        $user['ppLeagues'] = $this->ppLeagueRepository->getPPLeagues($activePPLeagueIds);

        $user['participations'] = $this->userParticipationRepository->getParticipations($userId);
        
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

        //2. get currently joined ppLTs for user
        $currentPPLTIds = $this->userParticipationRepository->getCurrentPPLeagueTypeIds($userId);

        $ppLTypesMap = $this->ppLeagueTypeRepository->getPPLTypesMap();

        foreach($ppLTypesMap as $typeKey => $typeItem){
            $allTypeIds = explode(',', $typeItem['ppLTIds']);
            if(!empty(array_intersect($currentPPLTIds, $allTypeIds ))){
                unset($ppLTypesMap[$typeKey]);
            }
        }
        print_r($currentPPLTIds);
        return  $ppLTypesMap;

        //1. get qualified ppLT ids for user
        $promotedPPLTIds = $this->userParticipationRepository->getPromotedPPLeagueTypeIds($userId);

        //2. get those ppLTs + 1 level  OR  same level if max, and level 1 for others
        $availablePPLeagueTypes = $this->ppLeagueTypeRepository->getAvailablePPLeagueTypes($promotedPPLTIds,$currentPPLTIds);

        return $availablePPLeagueTypes;        
    }

}
