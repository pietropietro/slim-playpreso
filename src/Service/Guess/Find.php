<?php

declare(strict_types=1);

namespace App\Service\Guess;

use App\Service\BaseService;
use App\Service\Match;
use App\Service\PPTournamentType;
use App\Repository\GuessRepository;


final class Find extends BaseService{
    public function __construct(
        protected GuessRepository $guessRepository,
        protected Match\Find $matchFindService,
        protected PPTournamentType\Find $ppTournamentTypeFindService,
    ){}

    private function enrich(&$guess){
        $guess['match'] = $this->matchFindService->getOne($guess['match_id']);
        $guess['ppTournamentType'] = $this->ppTournamentTypeFindService->getFromPPRoundMatch($guess['ppRoundMatch_id']);
    }

    public function getOne(int $id){
        $guess = $this->guessRepository->getOne($id);
        if(!$guess)return;
        $this->enrich($guess);
        return $guess;
    }
    

    public function lastLock(int $userId){
        return $this->guessRepository->lastLock($userId);
    }

    public function getUnlockedForUser(int $userId){
        $guesses = $this->guessRepository->getUnlockedForUser($userId);
        foreach($guesses as &$guess){
            $this->enrich($guess);
        }
        return $guesses;
    }

    public function getLockedForUser(int $userId){
        $guesses = $this->guessRepository->getLockedForUser($userId);
        foreach($guesses as &$guess){
            $this->enrich($guess);
        }
        return $guesses;
    }

    public function getForTeam(int $teamId, int $userId,  ?string $before=null, ?string $after=null){
        $guesses = $this->guessRepository->getForTeam($teamId, $userId, $before, $after);
        foreach($guesses as &$guess){
            $this->enrich($guess);
        }
        return $guesses;
    }

    public function getForLeague(int $leagueId, int $userId,  ?string $before=null, ?string $after=null){
        $guesses = $this->guessRepository->getForLeague($leagueId, $userId, $before, $after);
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

}