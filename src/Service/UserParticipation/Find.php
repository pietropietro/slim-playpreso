<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

use App\Service\RedisService;
use App\Repository\UserParticipationRepository;
use App\Repository\PPLeagueTypeRepository;
use App\Repository\PPLeagueRepository;
use App\Repository\PPRoundRepository;
use App\Repository\GuessRepository;


final class Find  extends Base {

    
    public function __construct(
        protected RedisService $redisService,
        protected UserParticipationRepository $userParticipationRepository,
        protected PPLeagueTypeRepository $ppLeagueTypeRepository,
        protected PPLeagueRepository $ppLeagueRepository,
        protected PPRoundRepository $ppRoundRepository,
        protected GuessRepository $guessRepository
        
    ){}

    public function getAllForPPL($ppLeagueId){
        return $this->userParticipationRepository->getLeagueParticipations($ppLeagueId); 
    }

    //TODO change playMode to ENUM
    public function getAll(int $userId, string $playMode, bool $active){
        $ups = $this->userParticipationRepository->getUserParticipations($userId, $playMode.'_id', $active, null);        
        foreach($ups as $upKey => $upItem){
            if($playMode === 'ppLeague'){
                $ups[$upKey] = $this->addPPLeagueData($ups[$upKey]);
            }
        }
        return $ups;
    }

    public function getTrophies(int $userId){
        $ppLeagueUps = $this->userParticipationRepository->getUserParticipations(
            $userId, 'ppLeague_id', false, (int)$_SERVER['PPLEAGUE_TROPHY_POSITION']
        );  

        $ppCupUps = $this->userParticipationRepository->getUserParticipations(
            $userId, 'ppCup_id', false, (int)$_SERVER['PPLEAGUE_TROPHY_POSITION']
        );  
        
        if(!$ppLeagueUps && !$ppCupUps) return null;

        foreach($ppLeagueUps as $upKey => $upItem){
            $ppLeagueUps[$upKey] = $this->addPPLeagueData($ppLeagueUps[$upKey]);
        }

        //also add data to cup trophies

        $trophies['ppLeagues'] = $ppLeagueUps;
        return $trophies;
    }
}