<?php

declare(strict_types=1);

namespace App\Service\PPArea;

use App\Service\RedisService;
use App\Service\League;
use App\Service\BaseService;
use App\Repository\PPAreaRepository;


final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected League\Find $leagueFindService,
        protected PPAreaRepository $ppAreaRepository,
    ) {}

    public function getOne(int $id){
        $ppArea = $this->ppAreaRepository->getOne($id);
        return $this->enrich($ppArea);
    }

    public function get(){
        $ppAreas = $this->ppAreaRepository->get();
        foreach ($ppAreas as &$ppA) {
            $this->enrich($ppA);
        }
        return $ppAreas;
    }

    private function enrich(&$ppArea){
        $ppArea['countries'] = $this->ppAreaRepository->getCountries($ppArea['id']);
        $ppArea['extra_tournaments'] = $this->getExtraTournaments($ppArea['id']);
    }

    public function getExtraTournaments($ppAreaId){
        return $this->leagueFindService->getPPAreaExtraTournaments($ppAreaId);
    }

}
