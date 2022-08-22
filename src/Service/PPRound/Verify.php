<?php

declare(strict_types=1);

namespace App\Service\PPRound;

use App\Repository\PPRoundRepository;
use App\Repository\PPRoundMatchRepository;

use App\Service\BaseService;

final class Verify  extends BaseService{
    public function __construct(
        protected PPRoundRepository $ppRoundRepository,
        protected PPRoundMatchRepository $ppRoundMatchRepository,
    ){}
    
    public function verify(int $matchId){
        $ppRounds = $this->findService->getForMatch($matchId);
        foreach ($ppRounds as $key => $round) {
            //check if other pproundmatches are also over
            //check if tournament needs more rounds 
            //if yes create
            //if no END tournament (if cupgroup -> )
        }
    
    }
}
