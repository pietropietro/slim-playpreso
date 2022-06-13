<?php

declare(strict_types=1);

namespace App\Service\PPLeague;

use App\Service\RedisService;
use App\Repository\PPLeagueRepository;
use App\Service\BaseService;

final class Join  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPLeagueRepository $ppLeagueRepository,
    ){}

    function join(int $typeId, int $userId){
        if($ppLT = $this->ppLeagueRepository->getJoinable($typeId)){
            return $ppLT;
        }
        $id = $this->ppLeagueRepository->create($typeId);
        return $this->ppLeagueRepository->get($id);
    }

}