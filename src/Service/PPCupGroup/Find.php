<?php

declare(strict_types=1);

namespace App\Service\PPCupGroup;

use App\Service\RedisService;
use App\Service\UserParticipation;
use App\Service\BaseService;
use App\Repository\PPCupGroupRepository;


final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPCupGroupRepository $ppCupGroupRepository,
        protected UserParticipation\Find $userParticipationService,
    ) {}

    public function getOne(int $id){
        return $this->ppCupGroupRepository->getOne($id);
    }
    
    public function getLevels(int $ppCupId) : array{
        $levels = [];
        $groups = $this->ppCupGroupRepository->getForCup($ppCupId);

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

    public function getJoinable(int $ppCupId) : ?int{
        return $this->ppCupGroupRepository->getJoinable($ppCupId)['id'] ?? null;
    }
}
