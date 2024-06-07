<?php

declare(strict_types=1);

namespace App\Service\PPRound;

use App\Service\RedisService;
use App\Service\BaseService;
use App\Service\PPRoundMatch;
use App\Service\Match;
use App\Repository\PPRoundRepository;
use App\Repository\GuessRepository;

final class Find  extends BaseService{
    
    public function __construct(
        protected RedisService $redisService,
        protected PPRoundMatch\Find $ppRoundMatchService,
        protected Match\Find $findMatchService,
        protected PPRoundRepository $ppRoundRepository,
        protected GuessRepository $guessRepository
    ){}

    public function getOne(
        int $id, 
        ?int $userId = null, 
        ?bool $withGuesses = false,
        ?bool $withMatchesStats = false 
    ){
        $ppRound = $this->ppRoundRepository->getOne($id);
        $ppRound['ppRoundMatches'] = $this->ppRoundMatchService->getForRound(
            $id, $userId, $withGuesses, $withMatchesStats
        );
        return $ppRound;
    }

    public function getForMatches(array $matchIds, bool $ids_only=false) : ?array {
        $ids = $this->ppRoundMatchService->getRoundIdsForMatches($matchIds);
        if($ids_only)return $ids ?? [];
        
        $ppRounds = [];
        if(is_array($ids)){
            foreach ($ids as $key => $id) {
                array_push($ppRounds, $this->getOne($id));
            }    
        }
        return $ppRounds;
    }

    public function has(string $type, int $typeId, int $round): bool{
        return $this->ppRoundRepository->has($type, $typeId, $round);
    }

    public function hasLiveMatch(string $type, int $typeId): bool{
        $latestRound = $this->ppRoundRepository->getForTournament(column: $type, valueId: $typeId, only_last: true);
        if(!$latestRound)return false;
        $matchIds = $this->ppRoundMatchService->getMatchesForRound($latestRound['id'], onlyIds:true); 
        if(!$matchIds)return false; 
        return $this->findMatchService->hasLiveMatch(ids: $matchIds);
    }

    public function getCurrentRoundNumber(string $type, int $typeId): int{
        $currentPPRound = $this->ppRoundRepository->getForTournament(column: $type, valueId: $typeId, only_last: true);
        return $currentPPRound['round'] ?? 0;
    }

    //returns array like (1,3) where 1 is verified matches count and 3 is tot matches in round
    public function verifiedInLatestRound(string $type, int $typeId): ?array{
        $latestPPRound = $this->ppRoundRepository->getForTournament(column: $type, valueId: $typeId, only_last: true);
        if(!$latestPPRound) return null;
        
        $ppRoundMatches = $this->ppRoundMatchService->getForRound(
            $latestPPRound['id'], 
        );
        $verifiedCount = 0;

        foreach ($ppRoundMatches as $pprm) {
            if($pprm['match']['verified_at'])$verifiedCount++;
        }
        
        return array( $verifiedCount, count($ppRoundMatches));
    }

    public function getLast(string $type, int $typeId){
        return $this->ppRoundRepository->getForTournament($type, $typeId, only_last: true);
    }

    public function getFromPPRM(int $ppRoundMatch_id){
        return $this->ppRoundRepository->getFromPPRM($ppRoundMatch_id);
    }

    
    public function getForTournament(string $type, int $typeId, ?int $userId) : ?array {
        $ppRounds = $this->ppRoundRepository->getForTournament($type, $typeId);
        
        foreach($ppRounds as $key => &$ppRound){
            $withMatchesStats = $key === array_key_last($ppRounds);
            $ppRound['ppRoundMatches'] = $this->ppRoundMatchService->getForRound(
                $ppRound['id'], 
                $userId,
                true, 
                $withMatchesStats
            );
                // $ppRound['best'] = $this->guessRepository->bestUsersInRound(
                //     ppRMids: array_column($ppRound['ppRoundMatches'], 'id'), 
                // );
        }
        return $ppRounds;
    }

    public function getUserCurrentRound(string $type, int $typeId, int $userId){
        $latestPPRound = $this->ppRoundRepository->getForTournament(column: $type, valueId: $typeId, only_last: true);
        return $this->ppRoundMatchService->getCurrentForUser($latestPPRound['id'], $userId);
    }

    public function getNextMatchInPPRound(string $type, int $typeId){
        $latestPPRound = $this->ppRoundRepository->getForTournament(column: $type, valueId: $typeId, only_last: true);
        $next = $this->findMatchService->getNextMatchInPPRound($latestPPRound['id']);
        if($next){
            unset($next['league']['standings']);
        }
        return $next;
    }
}
