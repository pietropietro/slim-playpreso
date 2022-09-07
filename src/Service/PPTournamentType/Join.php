<?php

declare(strict_types=1);

namespace App\Service\PPTournamentType;

use App\Service\BaseService;
use App\Service\PPLeague;
use App\Service\PPTournamentType;
use App\Service\User\Points;
use App\Service\UserParticipation;

final class Join  extends BaseService{
    public function __construct(
        protected PPLeague\Find $findPPleagueService,
        protected PPLeague\Start $startPPLeagueService,
        protected PPTournamentType\Find $findPPTournamentTypeService,
        protected Points $pointsService,
        protected UserParticipation\Create $createUpService,
        protected UserParticipation\Find $findUPservice,
    ) {}

    public function joinAvailable($userId, $typeId){
        $ppTournamentType = $this->findPPTournamentTypeService->getOne($typeId);

        if(!$ppTournamentType['is_ppCup']){
            $ppTournament = $this->findPPleagueService->getJoinable($typeId, $userId);
            $column = 'ppLeague_id';
        }else{
            //TODO
            // $ppTournament =
            // $column = 
        }
            
        if(!$this->pointsService->minus($userId, $ppTournamentType['cost'])){
            throw new Exception\User("couldn't join", 500);
        }

        if(!$insert = $this->createUpService->create($userId, $typeId, $ppTournament['id'])){
            throw new Exception\User("something went wrong", 500);
        };

        if($ppTournamentType['participants'] === count($this->findUPservice->getForTournament($column, $ppTournament['id']))){
            //todo startppcupservice
            $started = $ppTournamentType['is_ppCup'] ? null : $this->startPPLeagueService->start($ppTournament['id']);
        }

        return $ppTournament['id'];

    }
    
}