<?php

declare(strict_types=1);

namespace App\Service\Points;

use App\Exception\User;
use App\Repository\UserRepository;
use App\Service\BaseService;

final class Update extends BaseService
{
    public function __construct(
        protected UserRepository $userRepository,
    ) {}

    public function minus(int $userId, int $points){
        return $this->userRepository->minus($userId, $points);
    }

    public function plus(int $userId, ?int $points){
        if(!$points)return;
        return $this->userRepository->plus($userId, $points);
    }
}

