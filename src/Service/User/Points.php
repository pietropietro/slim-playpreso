<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Exception\User;
use App\Repository\UserRepository;
use App\Service\RedisService;


final class Points extends Base
{
    public function __construct(
        protected UserRepository $userRepository,
        protected RedisService $redisService,
    ) {}

    public function minus(int $userId, int $points){
        return $this->userRepository->minus($userId, $points);
    }

    public function plus(int $userId, int $points){
        return $this->userRepository->plus($userId, $points);
    }

    public function get(int $userId){
       return $this->userRepository->getPoints($userId);
    }
}

