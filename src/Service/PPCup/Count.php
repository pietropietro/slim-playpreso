<?php

declare(strict_types=1);

namespace App\Service\PPCup;

use App\Service\RedisService;
use App\Service\UserParticipation;
use App\Service\BaseService;
use App\Repository\PPCupRepository;
use App\Repository\PPCupGroupRepository;

final class Count extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected UserParticipation\Update $upService,
        protected PPCupRepository $ppCupRepository,
        protected PPCupGroupRepository $ppCupGroupRepository,
    ) {}

    public function updateGroups(int $id){
        $groupIds =  $this->ppCupGroupRepository->getCupGroupIds($id);

        foreach ($groupIds as $groupKey => $groupId) {
            $ups = $this->upService->update('ppCupGroup_id' , $groupId);
        }

        return true;
    }
}