<?php

declare(strict_types=1);

namespace App\Service\PPRound;

use App\Service\RedisService;
use App\Service\BaseService;
use App\Service\PPRoundMatch;
use App\Repository\PPRoundRepository;
use App\Repository\GuessRepository;

final class Find  extends BaseService{
    
    public function __construct(
        protected RedisService $redisService,
        protected PPRoundMatch\Find $ppRoundMatchService,
        protected PPRoundRepository $ppRoundRepository,
        protected GuessRepository $guessRepository
    ){}

    public function getOne(int $id, bool $withGuesses){
        $ppRound = $this->ppRoundRepository->getOne($id);
        $ppRound['ppRoundMatches'] = $this->ppRoundMatchService->getForRound($id, $withGuesses);
        return $ppRound;
    }

    public function getForMatches(array $matchIds) : array {
        $ppRounds = [];
        $ids = $this->ppRoundMatchService->getRoundIdsForMatches($matchIds);
        if(is_array($ids)){
            foreach ($ids as $key => $id) {
                array_push($ppRounds, $this->getOne($id, false));
            }    
        }
        return $ppRounds;
    }

    public function has(string $type, int $typeId, int $round): bool{
        return $this->ppRoundRepository->has($type, $typeId, $round);
    }
    
    //TODO change to ENUM type can be ppCupGroup_id OR ppLeague_id
    public function getForTournament(string $type, int $typeId) : ?array {
        $ppRounds = $this->ppRoundRepository->getForTournament($type, $typeId);
        foreach($ppRounds as $roundKey => $roundItem){
            $ppRounds[$roundKey]['ppRoundMatches'] = $this->ppRoundMatchService->getForRound($roundItem['id'], withGuesses: true);
            $ppRounds[$roundKey]['best'] = $this->guessRepository->bestUsersInRound(
                ppRMids: array_column($ppRounds[$roundKey]['ppRoundMatches'], 'id'), 
                limit: 3
            );
        }
        return $ppRounds;
    }
}
