<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\Guess;
use App\Service\PPRound;
use App\Repository\MatchRepository;

final class Verify extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected Guess\Verify $guessVerifyService,
        protected PPRound\Verify $ppRoundVerifyService,
    ) {}
    
    public function verify(int $matchId, int $homeScore, int $awayScore){
        $this->matchRepository->verify($matchId, $homeScore, $awayScore);
        $this->guessVerifyService->verify($matchId, $homeScore, $awayScore);
        $this->ppRoundVerifyService->verifyAfterMatch($matchId);
    }

}