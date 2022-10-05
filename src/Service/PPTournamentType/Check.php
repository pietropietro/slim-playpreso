<?php

declare(strict_types=1);

namespace App\Service\PPTournamentType;

use App\Service\BaseService;
use App\Service\PPTournamentType;
use App\Service\Points;

final class Check  extends BaseService{
    public function __construct(
        protected PPTournamentType\Find $findTournamentService,
        protected Points\Find $pointsService,
    ) {}
    
    public function check($userId, $typeId) :bool {

        if(!$this->isAllowed($userId, $typeId)){
            throw new \App\Exception\User("user not allowed", 401);
        }

        if(!$this->canAfford($userId, $typeId)){
            throw new \App\Exception\User("not enough points", 401);
        }

        //TODO ALSO CHECK PPTOURNAMENT CAN START i.e. has matches

        return true;
    }

    public function canAfford(int $userId, int $typeId){
        $userPoints = $this->pointsService->get($userId);
        $cost = $this->findTournamentService->getOne($typeId)['cost'];
        return $userPoints >= $cost;
    }

    public function isAllowed($userId, $typeId){
        $okIds = $this->findTournamentService->getAvailablePPLeaguesForUser($userId, only_ids: true);
        return in_array($typeId, $okIds);
    }
}
