<?php

declare(strict_types=1);

namespace App\Service\PPLeagueType;

use App\Service\RedisService;
use App\Repository\PPLeagueTypeRepository;
use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPLeagueTypeRepository $ppLeagueTypeRepository,
    ){}

    public function getOne($ppLeagueTypeId){
        return $this->ppLeagueTypeRepository->getOne($ppLeagueTypeId);
    }

}