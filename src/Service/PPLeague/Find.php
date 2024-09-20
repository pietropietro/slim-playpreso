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
        $this->enrich($ppLeague);
        return $ppLeague;
    }

    public function get(?int $ppttLevel = null, ?string $ppttName, ?bool $finished=null, ?bool $started=null){
        $ppLeagues = $this->ppLeagueRepository->get(
            null, 
            $ppttLevel, 
            $ppttName,
            $finished, 
            $started
        );

        foreach ($ppLeagues as &$ppLeague) {
            $this->enrich($ppLeague);
        }
        return $ppLeagues;
    }

    public function adminGetAllPaused(?bool $enrich = true){
        $ppLeagues = $this->ppLeagueRepository->getPaused();
        foreach ($ppLeagues as &$ppLeague) {
            $this->enrich($ppLeague);
        }
        return $ppLeagues;
    }

    private function enrich(&$ppLeague){
        $ppLeague['ppTournamentType'] = $this->findTournamentType->getOne($ppLeague['ppTournamentType_id']);
    }


    function getJoinable(int $ppTypeId){
        if(!$ppLeague = $this->ppLeagueRepository->getJoinable($ppTypeId)){
            $id = $this->ppLeagueRepository->create($ppTypeId);
            $ppLeague = $this->ppLeagueRepository->getOne($id);
        }
        $ppLeague['ppTournamentType'] = $this->findTournamentType->getOne($ppLeague['ppTournamentType_id']);
        return $ppLeague;
    }
    

}
