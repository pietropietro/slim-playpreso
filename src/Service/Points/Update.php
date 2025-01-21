<?php

declare(strict_types=1);

namespace App\Service\Points;

use App\Repository\UserRepository;
use App\Service\BaseService;

final class Update extends BaseService
{
    public function __construct(
        protected UserRepository $userRepository,
    ) {}

    public function minus(int $userId, int $points){
        if($this->userRepository->getPoints($userId) < $points){
            throw new \App\Exception\NotFound("can't afford", 400);
        }
        return $this->userRepository->minus($userId, $points);
    }

    public function plus(int $userId, ?int $points){
        if(!$points)return;
        return $this->userRepository->plus($userId, $points);
    }
}

