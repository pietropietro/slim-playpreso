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

    public function isAdmin(int $userId){
        return $this->userRepository->isAdmin($userId);
    }

    public function getOneFromUsername(string $username, ?bool $allColumns=false){
        if(!$id = $this->idFromUsername($username)) return null;
        return $this->getOne($id, $allColumns);
    }

    public function getOne(int $userId, ?bool $allColumns=false) 
    {
        if (!$allColumns && self::isRedisEnabled() === true && $cached = $this->getUserFromCache($userId)) {
            return $cached;
        } 
        
        $user = $this->getUserFromDb($userId, $allColumns);

        if (!$allColumns && self::isRedisEnabled() === true){
            $this->saveInCache($userId, (object) $user);
        }

        return $user;
    }


}
