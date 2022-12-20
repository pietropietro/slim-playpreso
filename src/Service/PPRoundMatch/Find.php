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

        foreach($ppRoundMatches as $key => $ppRM){        
            $ppRoundMatches[$key]['match'] = $this->matchFindService->getOne($ppRM['match_id']);
            if(!$withGuesses)continue;
            $ppRoundMatches[$key]['guesses'] = $this->guessRepository->getForPPRoundMatch($ppRM['id']);
        }
        return $ppRoundMatches;
    }

    public function getMatchesForRound(int $ppRoundId, ?bool $onlyIds = false) : ?array {
        return $this->ppRoundMatchRepository->getMatchIdsForRound($ppRoundId);
        //TODO enrich if needed
    }

    
    
    public function getRoundIdsForMatches(array $matchIds){
        return $this->ppRoundMatchRepository->getRoundIdsForMatches($matchIds);
    }
    
}

