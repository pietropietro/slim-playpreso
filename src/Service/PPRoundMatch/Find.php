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
    
    public function getForRound(int $ppRoundId, bool $withGuesses){
        $this->ppRoundMatchRepository->get($ppRoundId);
        foreach($ppRounds[$roundKey]['ppRoundMatches'] as $ppRMKey => $ppRMItem){        
            $ppRounds[$roundKey]['ppRoundMatches'][$ppRMKey]['match'] = $this->matchRepository->getOne($ppRMItem['match_id']);
            $ppRounds[$roundKey]['ppRoundMatches'][$ppRMKey]['guesses'] = $this->guessRepository->getForPPRoundMatch($ppRMItem['id']);
        }
    }

    public function getRoundIdsForMatch(int $matchId){
        return $this->ppRoundMatchRepository->getRoundIdsForMatch($matchId);
    }
    
}

