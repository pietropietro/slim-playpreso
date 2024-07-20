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

    public function enrich(&$guess, ?bool $withMatchStats = true){
        $guess['match'] = $this->matchFindService->getOne(
            id: $guess['match_id'], 
            withStats: $withMatchStats
        );
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

    public function getForUser(
        int $userId, 
        ?bool $includeMotd = true, 
        ?bool $locked = null, 
        ?bool $verified = null,
        ?string $verified_after = null,
        ?string $order = 'asc',
        ?int $page = 1, 
        ?int $limit = 200
    ){
        $offset = ($page - 1) * $limit;

        $guesses = $this->guessRepository->getForUser(
            $userId, 
            $includeMotd, 
            $locked, 
            $verified,
            $verified_after,
            $order,
            $offset,
            $limit
        );

        foreach($guesses as &$guess){
            $this->enrich(
                guess: $guess, 
                withMatchStats: false
            );
        }
        return $guesses;
    }

    public function getForTeam(int $teamId, int $userId,  ?string $from=null, ?string $to=null){
        $guesses = $this->guessRepository->getForTeam($teamId, $userId, $from, $to);
        foreach($guesses as &$guess){
            $this->enrich($guess);
        }
        return $guesses;
    }

    public function getForLeague(int $leagueId, int $userId,  ?string $from=null, ?string $to=null){
        $guesses = $this->guessRepository->getForLeague($leagueId, $userId, $from, $to);
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