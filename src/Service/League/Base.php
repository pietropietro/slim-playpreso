<?php

declare(strict_types=1);

namespace App\Service\League;

use App\Entity\User;
use App\Service\RedisService;
use App\Service\BaseService;

abstract class Base extends BaseService
{
    private const REDIS_KEY_HASMATCHES = 'league:%s-has-matches-in-week:%s';

    public function __construct(
        protected RedisService $redisService
    ) {
    }


    protected function getHasMatchesForNextWeeksFromCache(int $id, int $weeks)
    {
        $redisKey = sprintf(self::REDIS_KEY_HASMATCHES, $id, $weeks);
        $key = $this->redisService->generateKey($redisKey);
        if ($this->redisService->exists($key)) {
            $data = $this->redisService->get($key);
        } 
        return null;
    }

    
    protected function saveHasMatchesForNextWeeksInCache(int $id, int $weeks, array $result): void
    {
        $redisKey = sprintf(self::REDIS_KEY_HASMATCHES, $id, $weeks);
        $key = $this->redisService->generateKey($redisKey);
        $this->redisService->setex($key, $result, 86400);
    }

    // protected function deleteFromCache(int $userId): void
    // {
    //     $redisKey = sprintf(self::REDIS_KEY, $userId);
    //     $key = $this->redisService->generateKey($redisKey);
    //     $this->redisService->del([$key]);
    // }
}
