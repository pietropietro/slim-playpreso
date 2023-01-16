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
    
    public function getForRound(int $ppRoundId, ?bool $withGuesses = false, ?bool $onlyIds = false, ?int $userId=null) : ?array {
        $ppRoundMatches = $this->ppRoundMatchRepository->getForRound($ppRoundId, $onlyIds);
        if($onlyIds)return $ppRoundMatches;

        foreach($ppRoundMatches as &$ppRM){        
            $ppRM['match'] = $this->matchFindService->getOne($ppRM['match_id']);
            if(!$withGuesses)continue;
            $ppRM['guesses'] = $this->getPPRMGuesses($ppRM['id'], $userId);
        }
        return $ppRoundMatches;
    }

    private function getPPRMGuesses(int $id, ?int $userId = null){
        $guesses = $this->guessRepository->getForPPRoundMatch($id);

        //if 1 of the guesses is verified (i.e. is missed or match verified) 
        //return all guesses w/ predictions
        $verified = array_filter(
            array_column($guesses, 'verified_at'), 
            function ($ver) { return !!$ver;}
        );
        if(!empty($verified)) return $guesses;

        //if all guesses are locked, return w/ predictions
        $unlocked = array_filter(
            array_column($guesses, 'guessed_at'), 
            function ($guessed) { return !$guessed;}
        );
        if(empty($unlocked)) return $guesses;

        //otherwise return predictions only of currentUser guess (if any)
        foreach($guesses as &$guess){
            if($guess['user_id'] === $userId)continue;
            $guess['home'] = NULL;
            $guess['away'] = NULL;
        }
        return $guesses;
    }

    public function getMatchesForRound(int $ppRoundId, ?bool $onlyIds = false) : ?array {
        return $this->ppRoundMatchRepository->getMatchIdsForRound($ppRoundId);
        //TODO enrich if needed
    }

    public function getRoundIdsForMatches(array $matchIds){
        return $this->ppRoundMatchRepository->getRoundIdsForMatches($matchIds);
    }

    public function getParentPPRound(int $id){
        return $this->ppRoundMatchRepository->getParentPPRound($id);
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

