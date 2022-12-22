<?php

declare(strict_types=1);

namespace App\Service\PPRoundMatch;

use App\Repository\PPRoundMatchRepository;
use App\Repository\GuessRepository;
use App\Service\RedisService;
use App\Service\BaseService;
use App\Service\Match;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPRoundMatchRepository $ppRoundMatchRepository,
        protected guessRepository $guessRepository,
        protected Match\Find $matchFindService
    ){}
    
    public function getForRound(int $ppRoundId, ?bool $withGuesses = false, ?bool $onlyIds = false) : ?array {
        $ppRoundMatches = $this->ppRoundMatchRepository->getForRound($ppRoundId, $onlyIds);
        if($onlyIds)return $ppRoundMatches;

        foreach($ppRoundMatches as &$ppRM){        
            $ppRM['match'] = $this->matchFindService->getOne($ppRM['match_id']);
            if(!$withGuesses)continue;
            $ppRM['guesses'] = $this->getPPRMGuesses($ppRM['id']);
        }
        return $ppRoundMatches;
    }

    private function getPPRMGuesses($id){
        $guesses = $this->guessRepository->getForPPRoundMatch($id);
        //TODO 
        //if any of the guesses is not verified and not locked,
        //do not return other users prediction
        
        return $guesses;
    }

    public function getMatchesForRound(int $ppRoundId, ?bool $onlyIds = false) : ?array {
        return $this->ppRoundMatchRepository->getMatchIdsForRound($ppRoundId);
        //TODO enrich if needed
    }

    public function getRoundIdsForMatches(array $matchIds){
        return $this->ppRoundMatchRepository->getRoundIdsForMatches($matchIds);
    }

    public function getCurrentForUser(int $ppRoundId, int $userId){
        $ppRoundMatches = $this->ppRoundMatchRepository->getForRound($ppRoundId);
        foreach($ppRoundMatches as &$ppRM){        
            $ppRM['match'] = $this->matchFindService->getOne($ppRM['match_id']);
            $ppRM['guess'] = $this->guessRepository->getForPPRoundMatch($ppRM['id'], $userId);
        }
        return $ppRoundMatches;
    }
    
}

