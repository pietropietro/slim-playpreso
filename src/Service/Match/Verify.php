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
    
    public function verify(Object $eventObj, int $matchId){
        $this->matchRepository->verify($matchId, (int)$eventObj->Tr1, (int)$eventObj->Tr2);
        $this->guessVerifyService->verify($matchId, (int)$eventObj->Tr1, (int)$eventObj->Tr2);
        $this->ppRoundVerifyService->verify($matchId);
    }

}