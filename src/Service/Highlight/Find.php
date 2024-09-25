<?php

declare(strict_types=1);

namespace App\Service\Highlight;

use App\Repository\HighlightRepository;
use App\Service\RedisService;
use App\Service\BaseService;
use App\Service\Trophy;
use App\Service\Stats;
use App\Service\Match;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected Trophy\Find $trophyFindService,
        protected Stats\Find $statsFindService,
        protected HighlightRepository $highlightRepository,
    ){}

    private const REDIS_KEY_PRESO_HIGHLIGHTS = 'highlight_preso:%d';
    private const REDIS_KEY_FULLPRESOROUND_HIGHLIGHTS = 'highlight_fullpresoround:%d';

        
    public function getLatestPreso(int $limit){
        if (self::isRedisEnabled() === true ) {
            $redisKey = $this->redisService->generateKey(sprintf(self::REDIS_KEY_PRESO_HIGHLIGHTS, $limit));
            $cached = $this->redisService->get($redisKey); // This returns null if not found or the user data if found
            if($cached !== null)return $cached;
        }

        $presos = $this->statsFindService->lastPreso(10);

        if (self::isRedisEnabled() === true ) {
            $redisKey = $this->redisService->generateKey(sprintf(self::REDIS_KEY_PRESO_HIGHLIGHTS, $limit));
            $expiration = 1 * 60 * 60; 
            $this->redisService->setex($redisKey, $presos, $expiration); 
        }
        return $presos;

    }

    public function getLatestFullPresoRound(int $limit){
        if (self::isRedisEnabled() === true ) {
            $redisKey = $this->redisService->generateKey(sprintf(self::REDIS_KEY_FULLPRESOROUND_HIGHLIGHTS, $limit));
            $cached = $this->redisService->get($redisKey); // This returns null if not found or the user data if found
            if($cached !== null)return $cached;
        }

        $fullPresoRounds = $this->highlightRepository->getLatestFullPresoRound(null,$limit);

        if (self::isRedisEnabled() === true ) {
            $redisKey = $this->redisService->generateKey(sprintf(self::REDIS_KEY_FULLPRESOROUND_HIGHLIGHTS, $limit));
            $expiration = 5 * 60 * 60; 
            $this->redisService->setex($redisKey, $fullPresoRounds, $expiration); 
        }
        return $fullPresoRounds;

    }

}