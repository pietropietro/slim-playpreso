<?php

declare(strict_types=1);

namespace App\Service\PPRound;

use App\Service\RedisService;
use App\Repository\PPRoundRepository;
use App\Repository\PPRoundMatchRepository;
use App\Repository\GuessRepository;
use App\Repository\MatchRepository;

use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPRoundRepository $ppRoundRepository,
        protected PPRoundMatchRepository $ppRoundMatchRepository,
        protected guessRepository $guessRepository,
        protected matchRepository $matchRepository
    ){}

    public function getForMatch(int $matchId) : array {
        $ppRounds = [];
        $ids = $this->ppRoundMatchRepository->getRoundIdsWithMatch($matchId);
        foreach ($ids as $key => $id) {
            $ppRound = $this->ppRoundRepository->getOne($id);
            $ppRound['ppRoundMatches'] = $this->ppRoundMatchRepository->get($id);
            //pproundmatch service get w/match TODO
        }
        return $ppRounds;
    }
    
    //TODO change to ENUM type can be ppCupGroup_id OR ppLeague_id
    public function getForTournament(string $type, int $typeId){
        $ppRounds = $this->ppRoundRepository->getForTournament($type, $typeId);
        foreach($ppRounds as $roundKey => $roundItem){
            $ppRounds[$roundKey]['ppRoundMatches'] = $this->ppRoundMatchRepository->get($roundItem['id']);
            foreach($ppRounds[$roundKey]['ppRoundMatches'] as $ppRMKey => $ppRMItem){        
                $ppRounds[$roundKey]['ppRoundMatches'][$ppRMKey]['match'] = $this->matchRepository->getOne($ppRMItem['match_id']);
                $ppRounds[$roundKey]['ppRoundMatches'][$ppRMKey]['guesses'] = $this->guessRepository->getForPPRoundMatch($ppRMItem['id']);
            }
        }
        return $ppRounds;
    }
}