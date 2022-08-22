<?php

declare(strict_types=1);

namespace App\Service\PPRound;

use App\Repository\PPRoundRepository;
use App\Repository\PPRoundMatchRepository;

use App\Service\BaseService;
use App\Service\PPLeague;

final class Verify  extends BaseService{
    public function __construct(
        protected PPRoundRepository $ppRoundRepository,
        protected PPRoundMatchRepository $ppRoundMatchRepository,
        protected PPLeague\Verify $ppLeagueVerifyService
    ){}
    
    public function verify(int $matchId){
        $ppRounds = $this->findService->getForMatch($matchId);
        foreach ($ppRounds as $key => $round) {
            if(!$isFinished = $this->isFinished($ppRounds)) continue;
            
            if($round['ppLeague_id']){
                $this->$ppLeagueVerifyService->verify($round['ppLeague_id'], $round['round']);
                //TODO
                continue;
            }
            //CUP
            //$ppcupGroup = getppcupGroupservice->getOne(id)
            //check if tournament needs more rounds 
            //if yes create
            //if no END tournament (if cupgroup -> )
        }
    
    }

    public function isFinished(array $ppRound) : bool {
        foreach ($ppRound['ppRoundMatches'] as $key => $ppRM) {
            if(!$ppRM['match']['verified_at']) return false;
        }
        return true;
    }
}
