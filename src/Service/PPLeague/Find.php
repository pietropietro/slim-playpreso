<?php

declare(strict_types=1);

namespace App\Service\PPLeague;

use App\Service\RedisService;
use App\Service\PPLeagueTypeService;
use App\Repository\PPLeagueRepository;
use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPLeagueRepository $ppLeagueRepository,
        protected PPLeagueTypeService\Find $ppLTService
    ) {}

    public function getOne(int $ppLeagueId){
        $ppLeague = $this->ppLeagueRepository->getOne($ppLeagueId);
        $ppLeague['ppLeagueType'] = $this->$ppLTService->getOne($ppLeague['ppLeagueType_id']);
        return $ppLeague;

    }

    function getJoinable(int $typeId, int $userId){
        if($ppLeague = $this->ppLeagueRepository->getJoinable($typeId)){
            return $ppLeague;
        }
        $id = $this->ppLeagueRepository->create($typeId);
        return $this->ppLeagueRepository->getOne($id);
    }
    

}
