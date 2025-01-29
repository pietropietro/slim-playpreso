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
        protected PPTournamentType\Find $findPPTournamentTypeService,
        protected Points\Update $pointsService,
        protected UserParticipation\Create $createUpService,
        protected PPCup\Find $findPPCupService,
        protected PPCupGroup\Find $findPPCupGroupService,
    ) {}

    public function joinAvailable(int $userId, int $ppTypeId, bool $pay = true){
        $ppTournamentType = $this->findPPTournamentTypeService->getOne($ppTypeId);

        if(!$ppTournamentType['cup_format']){
            $ppTournament = $this->findPPleagueService->getJoinable($ppTypeId, $userId);
            $tournamentColumn = 'ppLeague_id';

        }else{
            $tournamentColumn = 'ppCupGroup_id';
            $ppTournament = $this->findPPCupService->getJoinable($ppTypeId, $userId);
            if(!$ppTournament){
                throw new \App\Exception\User("could not join", 400);
            }
            $ppTournamentGroup =  $this->findPPCupGroupService->getNotFull($ppTournament['id'], level: 1);
        }

        if(!$ppTournament || $ppTournamentType['cup_format'] && !$ppTournamentGroup){
            throw new \App\Exception\User("could not join", 500);
        }

        //promoted users don't pay 
        if($pay && !$this->pointsService->minus($userId, $ppTournamentType['cost'])){
            throw new \App\Exception\User("couldn't afford", 500);
        }

        if(!$insert = $this->createUpService->create($userId, $ppTypeId, $ppTournament['id'], isset($ppTournamentGroup) ? $ppTournamentGroup['id'] : null)){
            error_log('could not create up for user '.$userId.' and Type '.$ppTypeId);
            return false;
        };
        
        return $ppTournament['id'];
    }
    
}