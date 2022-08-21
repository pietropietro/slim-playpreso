<?php

declare(strict_types=1);

namespace App\Service\PPCup;

use App\Service\RedisService;
use App\Service\UserParticipation;
use App\Repository\PPCupRepository;
use App\Repository\PPCupGroupRepository;
use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected UserParticipation\Find $userParticipationService,
        protected PPCupRepository $ppCupRepository,
        protected PPCupGroupRepository $ppCupGroupRepository,

    ) {}

    public function getOne($ppCupId){
        return $this->ppCupRepository->getOne($ppCupId);
    }

    public function getLevels($ppCupId){
       
        $levels = [];
        $groups = $this->ppCupGroupRepository->getGroupsForCup($ppCupId);

        foreach($groups as $group){
            $group['userParticipations'] = $this->userParticipationService->getForTournament('ppCupGroup_id', $group['id']);
            $currentLevel = $group['level'];
            if(!in_array($currentLevel, array_keys($levels))){
                $levels[$currentLevel] = [];
            }
            array_push($levels[$currentLevel], $group);
        }
        return $levels;
    }
}
