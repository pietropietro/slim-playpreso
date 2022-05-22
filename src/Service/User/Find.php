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

        $ppLTypesMap = $this->ppLeagueTypeRepository->getPPLTypesMap();
        $promotedPPLTIds = $this->userParticipationRepository->getPromotedPPLeagueTypeIds($userId);

        $currentPPLTIds = $this->userParticipationRepository->getCurrentPPLeagueTypeIds($userId);

        foreach($ppLTypesMap as $typeKey => $typeItem){
            $allTypeIds = explode(',', $typeItem['ppLTIds']);

            //remove user currently joined ppLTs
            if(!!$currentPPLTIds && !empty(array_intersect($currentPPLTIds, $allTypeIds ))){
                unset($ppLTypesMap[$typeKey]);
                continue;
            }

            $difference = !!$promotedPPLTIds ? array_values(array_diff($allTypeIds, $promotedPPLTIds)) : $allTypeIds;
            $ppLTypesMap[$typeKey]['level'] = count($allTypeIds) - count($difference) +1 ;
            $ppLTypesMap[$typeKey]['nextId'] = $difference[0];
            
        }
        return  array_values($ppLTypesMap);
    }

}
