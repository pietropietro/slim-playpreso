<?php

declare(strict_types=1);

namespace App\Service\PPCup;

use App\Service\RedisService;
use App\Service\BaseService;
use App\Service\PPCupGroup;
use App\Service\PPTournamentType;
use App\Repository\PPCupRepository;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPCupRepository $ppCupRepository,
        protected PPCupGroup\Find $ppCupGroupFindService,
        protected PPTournamentType\Find $ppTournamentTypeFindService
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
        return $ppCup;
    }

}
