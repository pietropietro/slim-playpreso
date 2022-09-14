<?php

declare(strict_types=1);

namespace App\Service\Points;

use App\Exception\User;
use App\Repository\UserRepository;

final class Find
{
    public function __construct(
        protected UserRepository $userRepository,
    ) {}

    public function get(int $userId){
       return $this->userRepository->getPoints($userId);
    }
}

