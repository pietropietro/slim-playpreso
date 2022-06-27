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
        $ups = $this->userParticipationRepository->getParticipationsForUser($userId, $playMode.'_id', $active, null);        
        foreach($ups as $upKey => $upItem){
            if($playMode === 'ppLeague'){
                $ups[$upKey] = $this->addPPLeagueData($ups[$upKey]);
            }
        }
        return $ups;
    }

    public function getTrophies(int $userId, string $playMode){
        $ups = $this->userParticipationRepository->getParticipationsForUser($userId, $playMode.'_id', false, (int)$_SERVER['PPLEAGUE_TROPHY_POSITION']);        
        foreach($ups as $upKey => $upItem){
            if($playMode === 'ppLeague'){
                $ups[$upKey] = $this->addPPLeagueData($ups[$upKey]);
            }
        }
        return $ups;
    }
}