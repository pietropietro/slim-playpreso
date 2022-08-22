<?php

declare(strict_types=1);

namespace App\Service\PPRoundMatch;

use App\Service\RedisService;
use App\Service\BaseService;
use App\Repository\PPRoundMatchRepository;
use App\Repository\GuessRepository;
use App\Repository\MatchRepository;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPRoundMatchRepository $ppRoundMatchRepository,
        protected guessRepository $guessRepository,
        protected matchRepository $matchRepository
    ){}
    
    public function getForRound(int $ppRoundId, bool $withGuesses) : ?array{
        $ppRoundMatches = $this->ppRoundMatchRepository->getForRound($ppRoundId);
        foreach($ppRoundMatches as $key => $ppRM){        
            $ppRoundMatches[$key]['match'] = $this->matchRepository->getOne($ppRM['match_id']);
            if($withGuesses){
                $ppRoundMatches[$key]['guesses'] = $this->guessRepository->getForPPRoundMatch($ppRM['id']);
            }
        }
        return $ppRoundMatches;
    }

    public function getRoundIdsForMatch(int $matchId){
        return $this->ppRoundMatchRepository->getRoundIdsForMatch($matchId);
    }
    
}

