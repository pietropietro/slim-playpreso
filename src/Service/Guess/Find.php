<?php

declare(strict_types=1);

namespace App\Service\Guess;

use App\Service\BaseService;
use App\Service\Match;
use App\Service\PPRound;
use App\Service\PPRoundMatch;
use App\Repository\GuessRepository;


final class Find extends BaseService{
    public function __construct(
        protected GuessRepository $guessRepository,
        protected Match\Find $matchFindService,
        protected PPRound\Find $ppRoundFindService,
        protected PPRoundMatch\Find $ppRoundMatchFindService,
    ){}
    
    public function getForUser(int $userId, ?bool $verified=null, ?int $limit = null) : array {
        return $this->guessRepository->getForUser($userId, $verified, $limit);
    }

    public function lastLock(int $userId){
        return $this->guessRepository->lastLock($userId);
    }

    public function notLocked(int $userId){
        $guesses = $this->guessRepository->getForUser($userId, false);
        foreach($guesses as &$guess){
            $guess['match'] = $this->matchFindService->getOne($guess['match_id']);
            $guess['ppTournamentType'] = $this->getGuessPPTournamentType($guess['ppRoundMatch_id']);
        }
        return $guesses;
    }

    private function getGuessPPTournamentType(int $ppRoundMatchId){
        $ppRound = $this->ppRoundMatchFindService->getParentPPRound($ppRoundMatchId);
        if(!$ppRound)return;
        return $ppTournamentType = $this->ppRoundFindService->getParentTournamentType($ppRound['id']);
    }

}