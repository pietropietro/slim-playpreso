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

    private function enrich(&$guess){
        $guess['match'] = $this->matchFindService->getOne($guess['match_id']);
        $guess['ppTournamentType'] = $this->getGuessPPTournamentType($guess['ppRoundMatch_id']);
    }
    

    public function lastLock(int $userId){
        return $this->guessRepository->lastLock($userId);
    }

    public function getNext(int $userId){
        $guesses = $this->guessRepository->getNext($userId);
        foreach($guesses as &$guess){
            $this->enrich($guess);
        }
        return $guesses;
    }

    public function getLast(int $userId, ?string $afterString = null,?int $limit=null){
        $guesses = $this->guessRepository->getLast($userId, $afterString, $limit);
        foreach($guesses as &$guess){
            $this->enrich($guess);
        }
        return $guesses;
    }

    public function getNeedReminder(){
        return $this->guessRepository->getNeedReminder();
    }

    private function getGuessPPTournamentType(int $ppRoundMatchId){
        $ppRound = $this->ppRoundMatchFindService->getParentPPRound($ppRoundMatchId);
        if(!$ppRound)return;
        return $ppTournamentType = $this->ppRoundFindService->getParentTournamentType($ppRound['id']);
    }

}