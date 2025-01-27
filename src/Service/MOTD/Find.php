<?php

declare(strict_types=1);

namespace App\Service\MOTD;

use App\Repository\MOTDRepository;
use App\Service\RedisService;
use App\Service\BaseService;
use App\Service\PPRoundMatch;
use App\Service\Match;
use App\Service\Guess;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected MOTDRepository $motdRepository,
        protected PPRoundMatch\Find $ppRoundMatchFindService,
        protected Match\Find $matchFindService,
        protected Guess\Find $guessFindService,
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
        ?int $userId = null,
        ?bool $withGuesses = false,
        ?bool $withMatchStats = true,
    ){
        $motdPPRM = $this->motdRepository->getMotd($dateString);
        if($userId || $withGuesses){
            $this->ppRoundMatchFindService->enrich(
                $motdPPRM,
                withGuesses: $withGuesses,
                userId: $userId, 
                withUserGuess: isset($userId),
                withMatchStats: true
            );
        }
        return $motdPPRM;
    }

    public function hasMotd(){
        return $this->motdRepository->hasMotd();
    }

    public function getWeeklyStandings(?int $userId=null){
        $best = $this->motdRepository->getWeeklyStandings(null,6);
        $returnArray = array(
            "best" => $best
        );
        if($userId && !in_array($userId, array_column($best,'user_id'))){
            array_pop($returnArray['best']);
            if($userStat = $this->motdRepository->getWeeklyStandings($userId)){
                $returnArray['currentUserStat'] = $userStat[0];
            } 
        }
        return $returnArray;
    }

    public function getLastForUser(int $userId, ?int $howMany=30){
        $motdGuesses =  $this->motdRepository->getLastForUser($userId, $howMany);
        foreach ($motdGuesses as &$guess) {
            $this->guessFindService->enrich($guess, false);
        }
        return $motdGuesses;
    }

}