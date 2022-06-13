<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Exception\User;
use App\Repository\UserRepository;
use App\Repository\PPLeagueTypeRepository;
use App\Service\RedisService;


final class Points extends Base
{
    public function __construct(
        protected UserRepository $userRepository,
        protected RedisService $redisService,
        protected PPLeagueTypeRepository $ppLeagueTypeRepository
    ) {}

    public function payPPLT($userId, $typeId){
        $cost = $this->ppLeagueTypeRepository->getOne($typeId)['cost'];
        return $this->userRepository->minus($userId, $cost);
    }

    public function get($userId){
       return $this->userRepository->getPoints($userId);
    }
}

