<?php

declare(strict_types=1);

namespace App\Service\PPLeague;

use App\Service\RedisService;
use App\Repository\PPLeagueRepository;
use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPLeagueRepository $ppLeagueRepository,
    ) {}

    public function getOne($ppLeagueId){
        return $this->ppLeagueRepository->getOne($ppLeagueId);
    }

    function getJoinable(int $typeId, int $userId){
        if($ppLT = $this->ppLeagueRepository->getJoinable($typeId)){
            return $ppLT;
        }
        $id = $this->ppLeagueRepository->create($typeId);
        return $this->ppLeagueRepository->getOne($id);
    }
    

}
