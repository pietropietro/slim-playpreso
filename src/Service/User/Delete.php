<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Repository\UserRepository;
use App\Service\RedisService;

final class Delete extends Base
{
    public function __construct(
        protected UserRepository $userRepository,
        protected RedisService $redisService
    ) {
    }


    public function anonymize(int $userId)
    {
        $user = $this->getUserFromDb($userId);
        if(!$user)return;

        $res = $this->userRepository->anonymizeUser($userId);
        
        if (self::isRedisEnabled() === true) {
            $this->deleteFromCache($userId);
        }

        return $res;
    }
}
