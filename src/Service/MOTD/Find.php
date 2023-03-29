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

    //returns last 7 motds
    public function getLatestMotds(?int $userId = null){
        $ppRMs = $this->motdRepository->getLatestMotds(7);
        if(!$ppRMs) return null;
        
        foreach($ppRMs as &$ppRM){
            $this->ppRoundMatchFindService->enrich(
                $ppRM,
                userId: $userId, 
                withUserGuess: true,
                withMatchStats: true,
                withPPRMStats: true
            );
            $ppRM['can_lock'] = $this->matchFindService->isBeforeStartTime($ppRM['match_id']);
        }
        
        return $ppRMs;
    }

    //before 7am gmt+1 gives back yesterday's motd
    public function getMotd(
        ?string $dateString = null,
        ?int $userId = null
    ){
        $motdPPRM = $this->motdRepository->getMotd($dateString);
        if($userId){
            $this->ppRoundMatchFindService->enrich(
                $motdPPRM,
                userId: $userId, 
                withUserGuess: true,
                withMatchStats: false
            );
        }
        return $motdPPRM;
    }

    public function hasMotd(){
        return $this->motdRepository->hasMotd();
    }

    public function getWeeklyStandings(?int $userId=null){
        return $this->motdRepository->getWeeklyStandings();
    }
}