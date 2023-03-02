<?php

declare(strict_types=1);

namespace App\Service\MOTD;

use App\Repository\MOTDRepository;
use App\Service\RedisService;
use App\Service\BaseService;
use App\Service\PPRoundMatch;
use App\Service\Match;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected MOTDRepository $motdRepository,
        protected PPRoundMatch\Find $ppRoundMatchFindService,
        protected Match\Find $matchFindService
    ){}

    public function getCurrentMotd(?int $userId = null){
        $ppRM = $this->motdRepository->getCurrentMotd();
        if(!$ppRM) return null;
        $this->ppRoundMatchFindService->enrich(
            $ppRM,
            userId: $userId, 
            withUserGuess: true,
            withMatchStats: true
        );
        $ppRM['can_lock'] = $this->matchFindService->isBeforeStartTime($ppRM['match_id']);
        return $ppRM;
    }

    public function getMotd(?string $dateString = null){
        return $this->motdRepository->getMotd($dateString);
    }

    public function hasMotd(){
        return $this->motdRepository->hasMotd();
    }

    public function getWeeklyStandings(?int $userId=null){
        return $this->motdRepository->getWeeklyStandings();
    }
}