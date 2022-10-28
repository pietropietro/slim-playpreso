<?php

declare(strict_types=1);

namespace App\Service\PPCup;

use App\Service\RedisService;
use App\Service\BaseService;
use App\Service\PPCupGroup;
use App\Service\PPTournamentType;
use App\Service\UserParticipation;
use App\Repository\PPCupRepository;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPCupRepository $ppCupRepository,
        protected PPCupGroup\Find $ppCupGroupFindService,
        protected PPTournamentType\Find $ppTournamentTypeFindService,
        protected UserParticipation\Find $upFindService
    ) {}

    public function getOne($uniqueVal, bool $is_slug = false){
        $ppCup = $this->ppCupRepository->getOne($uniqueVal, $is_slug);
        return $this->enrich($ppCup, with_levels: true);
    }

    public function getAll(?int $ppTournamentTypeId){
        $ppCups = $this->ppCupRepository->get(null, $ppTournamentTypeId);
        foreach ($ppCups as $key => $cup) {
            $ppCups[$key] = $this->enrich($cup, with_levels: false);
        }
        return $ppCups;
    }

    private function enrich($ppCup, bool $with_levels){
        $ppCup['ppTournamentType'] = $this->ppTournamentTypeFindService->getOne($ppCup['ppTournamentType_id']);
        if($with_levels)$ppCup['levels'] = $this->ppCupGroupFindService->getLevels($ppCup['id']);
        $ppCup['user_count'] = $this->upFindService->countParticipations('ppCup_id', $ppCup['id']);
        $ppCup['can_join'] = (bool)$this->getJoinableGroup($ppCup['id']);
        return $ppCup;
    }

    function getJoinableGroup(int $typeId){
        if(!$ppCup = $this->ppCupRepository->getOne($typeId)) return;
        return $this->ppCupGroupFindService->getJoinable($ppCup['id']);
    }

}
