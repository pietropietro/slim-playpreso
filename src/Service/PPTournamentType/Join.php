<?php

declare(strict_types=1);

namespace App\Service\PPTournamentType;

use App\Service\BaseService;
use App\Service\PPLeague;
use App\Service\PPCup;
use App\Service\PPCupGroup;
use App\Service\PPTournamentType;
use App\Service\Points;
use App\Service\UserParticipation;

final class Join  extends BaseService{
    public function __construct(
        protected PPLeague\Find $findPPleagueService,
        protected PPLeague\Start $startPPLeagueService,
        protected PPTournamentType\Find $findPPTournamentTypeService,
        protected Points\Update $pointsService,
        protected UserParticipation\Create $createUpService,
        protected UserParticipation\Find $findUPservice,
        protected PPCup\Find $findPPCupService,
        protected PPCupGroup\Find $findPPCupGroupService,
    ) {}

    public function joinAvailable(int $userId, int $ppTypeId){
        $ppTournamentType = $this->findPPTournamentTypeService->getOne($ppTypeId);

        if(!$ppTournamentType['cup_format']){
            $ppTournament = $this->findPPleagueService->getJoinable($ppTypeId, $userId);
            $column = 'ppLeague_id';
        }else{
            $column = 'ppCup_id';
            $ppTournament = $this->findPPCupService->getJoinable($ppTypeId, $userId);
            $ppTournamentGroup =  $this->findPPCupGroupService->getJoinable($ppTournament['id']);
        }

        if(!$ppTournament) throw new \App\Exception\User("could not join", 500);

        if(!$this->pointsService->minus($userId, $ppTournamentType['cost'])){
            throw new \App\Exception\User("couldn't afford", 500);
        }

        if(!$insert = $this->createUpService->create($userId, $ppTypeId, $ppTournament['id'], $ppTournamentGroup ? $ppTournamentGroup['id'] : null)){
            throw new \App\Exception\User("something went wrong", 500);
        };
        
        //TODO
        if($ppTournamentType['participants'] === count($this->findUPservice->getForTournament($column, $ppTournament['id']))){
            //TODO startppcupservice
            $started = $ppTournamentType['cup_format'] ? null 
            : $this->startPPLeagueService->start($ppTournament['id'], $ppTournamentType['id']);
        }

        return $ppTournament['id'];

    }
    
}