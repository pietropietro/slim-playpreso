<?php

declare(strict_types=1);

namespace App\Service\PPTournamentType;

use App\Service\BaseService;
use App\Service\PPTournamentType;
use App\Service\UserParticipation;
use App\Service\Points;
use App\Repository\PPCupRepository;

final class Check  extends BaseService{
    public function __construct(
        protected PPTournamentType\Find $findTournamentService,
        protected Points\Find $pointsService,
        protected PPCupRepository $ppCupRepository,
        protected UserParticipation\Find $findUpService,
    ) {}
    
    public function check($userId, $typeId) :bool {

        $ppTT = $this->findTournamentService->getOne($typeId);
        
        if(!$ppTT['cup_format'] && !$this->isAllowedInPPLeague($userId, $typeId)){
            throw new \App\Exception\User("user not allowed p-league", 403);
        }
        else if(!$this->isAllowedInPPCup($userId, $typeId)){
            throw new \App\Exception\User("user not allowed in p-cup", 403);
        }

        if(!$this->canAfford($userId, $typeId)){
            throw new \App\Exception\User("not enough points", 403);
        }

        //TODO ALSO CHECK PPTOURNAMENT CAN START i.e. has matches

        return true;
    }

    public function canAfford(int $userId, int $typeId){
        $userPoints = $this->pointsService->get($userId);
        $cost = $this->findTournamentService->getOne($typeId)['cost'];
        return $userPoints >= $cost;
    }

    public function isAllowedInPPLeague($userId, $typeId){
        $okIds = $this->findTournamentService->getAvailablePPLeaguesForUser($userId);
        return in_array($typeId, $okIds);
    }

    public function isAllowedInPPCup(int $userId,int $typeId){
        return !$this->findUpService->isUserInTournamentType($userId, $typeId);
    }


    public function canCreateCup(int $ppTournamentType_id){
        if((bool)$this->ppCupRepository->getJoinable($ppTournamentType_id)){
            throw new \App\Exception\User("forbidden – cannot create p-cup", 403);
        }
        return true;
    }
}
