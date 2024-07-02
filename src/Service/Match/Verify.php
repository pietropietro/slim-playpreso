<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\Guess;
use App\Service\PPRound;
use App\Service\MOTD;
use App\Repository\MatchRepository;

final class Verify extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected Guess\Verify $guessVerifyService,
        protected PPRound\Verify $ppRoundVerifyService,
        protected MOTD\Leader $motdLeaderService,
    ) {}
    
    public function verify(int $matchId, int $homeScore, int $awayScore, ?string $notes=null){

        if($homeScore < 0 || $awayScore < 0 ){
            throw new \App\Exception\Match('score invalid', 500);
        }
        $this->matchRepository->verify($matchId, $homeScore, $awayScore, $notes);
        $this->guessVerifyService->verify($matchId, $homeScore, $awayScore);
        $this->motdLeaderService->checkIfCalculate($matchId);
        $this->ppRoundVerifyService->verifyAfterMatch($matchId);
    }

}