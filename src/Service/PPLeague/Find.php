<?php

declare(strict_types=1);

namespace App\Service\PPLeague;

use App\Service\RedisService;
use App\Service\PPTournamentType;
use App\Repository\PPLeagueRepository;
use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPLeagueRepository $ppLeagueRepository,
        protected PPTournamentType\Find $findTournamentType
    ) {}

    public function getOne(int $ppLeagueId){
        if(!$ppLeague = $this->ppLeagueRepository->getOne($ppLeagueId)) return;
        $ppLeague['ppTournamentType'] = $this->findTournamentType->getOne($ppLeague['ppTournamentType_id']);
        return $ppLeague;

    }

    function getJoinable(int $ppTypeId, int $userId){
        if(!$ppLeague = $this->ppLeagueRepository->getJoinable($ppTypeId)){
            $id = $this->ppLeagueRepository->create($ppTypeId);
            $ppLeague = $this->ppLeagueRepository->getOne($id);
        }
        $ppLeague['ppTournamentType'] = $this->findTournamentType->getOne($ppLeague['ppTournamentType_id']);
        return $ppLeague;
    }
    

}
