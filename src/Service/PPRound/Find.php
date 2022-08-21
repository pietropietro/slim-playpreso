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
    
    //change to ENUM 
    //type can be ppCupGroup_id OR ppLeague_id
    public function getAll($type, $typeId){
        $ppRounds = $this->ppRoundRepository->getAllFor($type, $typeId);
        foreach($ppRounds as $roundKey => $roundItem){
            $ppRounds[$roundKey]['ppRoundMatches'] = $this->ppRoundMatchRepository->getAllRound($roundItem['id']);
            foreach($ppRounds[$roundKey]['ppRoundMatches'] as $ppRMKey => $ppRMItem){        
                $ppRounds[$roundKey]['ppRoundMatches'][$ppRMKey]['match'] = $this->matchRepository->getOne($ppRMItem['match_id']);
                $ppRounds[$roundKey]['ppRoundMatches'][$ppRMKey]['guesses'] = $this->guessRepository->getForPPRoundMatch($ppRMItem['id']);
            }
        }
        return $ppRounds;
    }
}