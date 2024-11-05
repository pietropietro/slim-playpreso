<?php

declare(strict_types=1);

namespace App\Service\Trophy;

use App\Service\BaseService;
use App\Service\RedisService;
use App\Repository\UserParticipationRepository;
use App\Repository\PPTournamentTypeRepository;

final class Find extends BaseService{

    public function __construct(
        protected RedisService $redisService,
        protected UserParticipationRepository $userParticipationRepository,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
    ){}

    public function getTrophies(int $userId, ?string $afterDate = null, ?bool $just_count=false){
        $ppLeagueUps = $this->userParticipationRepository->getForUser(
            $userId, 
            'ppLeague_id', 
            started: null, 
            finished: true, 
            minPosition: 1,
            updatedAfter: $afterDate
        );  

        $ppCupWins = $this->userParticipationRepository->getCupWins($userId);

        $trophiesUP = array_merge($ppLeagueUps, $ppCupWins);
        if($just_count) return count($trophiesUP);
        foreach ($trophiesUP as &$trophyUP) {
            $trophyUP['ppTournamentType'] = $this->ppTournamentTypeRepository->getOne($trophyUP['ppTournamentType_id']);
        }
        return $trophiesUP;
    }
    
    private const REDIS_KEY_LATEST_TROPHIES = 'highlights-trophies-limit:%d';

    public function getLatestTrophies(int $limit = 5){

        if (self::isRedisEnabled() === true ) {
            $cachedList = $this->getListFromCache($limit);
            if ($cachedList !== null) {
                return $cachedList;
            }
        } 
        return $this->calculateList($limit);
    }

    protected function getListFromCache(int $limit)
    {
        $redisKey = $this->redisService->generateKey(sprintf(self::REDIS_KEY_LATEST_TROPHIES, $limit));
        return $this->redisService->get($redisKey); // This returns null if not found or the user data if found
    }

    private function calculateList(int $limit){
        $ppLeagueUps = $this->userParticipationRepository->getForUser(
            userId: null, 
            type: 'ppLeague_id', 
            started: null, 
            finished: true, 
            minPosition: 1,
            updatedAfter: null,
            updatedBefore: null,
            limit: $limit,
            sorted: true
        );  

        $ppCupWins = $this->userParticipationRepository->getCupWins(
            userId: null,
            limit: $limit
        );

        $mergedArray = array_merge($ppLeagueUps, $ppCupWins);
        usort($mergedArray, function($a, $b) {
            return strtotime($b['updated_at']) - strtotime($a['updated_at']);
        });
        $mostRecentFive = array_slice($mergedArray, 0, $limit);

        foreach ($mostRecentFive as &$trophyUP) {
            $trophyUP['ppTournamentType'] = $this->ppTournamentTypeRepository->getOne($trophyUP['ppTournamentType_id']);
        }

        if (self::isRedisEnabled() === true ) {
            $this->saveListInCache($mostRecentFive, $limit);
        }

        return $mostRecentFive;
    }

    protected function saveListInCache(array $trophies ,int $limit): void
    {
        $redisKey = $this->redisService->generateKey(sprintf(self::REDIS_KEY_LATEST_TROPHIES, $limit));
        $expiration = 3 * 60 * 60; 
        $this->redisService->setex($redisKey, $trophies, $expiration); 
    }

}
