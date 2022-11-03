<?php

declare(strict_types=1);

namespace App\Service\PPTournamentType;

use App\Service\BaseService;
use App\Service\PPLeague;
use App\Service\PPCup;
use App\Service\PPCupGroup;
use App\Service\PPTournamentType;
use App\Service\PPTournament;
use App\Service\Points;
use App\Service\UserParticipation;

final class Join  extends BaseService{
    public function __construct(
        protected PPLeague\Find $findPPleagueService,
        protected PPTournamentType\Find $findPPTournamentTypeService,
        protected Points\Update $pointsService,
        protected UserParticipation\Create $createUpService,
        protected PPCup\Find $findPPCupService,
        protected PPCupGroup\Find $findPPCupGroupService,
        protected PPTournament\VerifyAfterJoin $verify,
    ) {}

    public function joinAvailable(int $userId, int $ppTypeId){
        $ppTournamentType = $this->findPPTournamentTypeService->getOne($ppTypeId);

        if(!$ppTournamentType['cup_format']){
            $ppTournament = $this->findPPleagueService->getJoinable($ppTypeId, $userId);
            $tournamentColumn = 'ppLeague_id';
        }else{
            $tournamentColumn = 'ppCup_id';
            $ppTournament = $this->findPPCupService->getJoinable($ppTypeId, $userId);
            $ppTournamentGroup =  $this->findPPCupGroupService->getJoinable($ppTournament['id']);
        }

        if(!$ppTournament || $ppTournamentType['cup_format'] && !$ppTournamentGroup){
            throw new \App\Exception\User("could not join", 500);
        }

        if(!$this->pointsService->minus($userId, $ppTournamentType['cost'])){
            throw new \App\Exception\User("couldn't afford", 500);
        }

        if(!$insert = $this->createUpService->create($userId, $ppTypeId, $ppTournament['id'], $ppTournamentGroup ? $ppTournamentGroup['id'] : null)){
            throw new \App\Exception\User("something went wrong", 500);
        };
        
        $this->verify->afterJoined($tournamentColumn, $ppTournamentGroup['id'] ?? $ppTournament['id'], $ppTournamentType['id']);

        return $ppTournament['id'];
    }
    
}