<?php

declare(strict_types=1);

namespace App\Service\PPRound;

use App\Service\BaseService;
use App\Service\PPLeague;
use App\Service\UserParticipation;
use App\Service\PPRound;


final class Verify extends BaseService{
    public function __construct(
        protected PPRound\Find $findService,
        protected PPLeague\Verify $ppLeagueVerifyService,
        protected UserParticipation\Update $updateUpService
    ){}
    
    public function verifyAfterMatch(int $matchId){
        $ppRoundIds = $this->findService->getForMatches(array($matchId), ids_only: true);
        foreach ($ppRoundIds as $key => $id) {    
            $this->verify($id);
        }
    }

    public function verify($id){
        $ppRound=$this->findService->getOne($id, false);
        if($ppRound['ppLeague_id']){
            $this->updateUpService->update('ppLeague_id', $ppRound['ppLeague_id']);
            if($this->isRoundFinished($ppRound)){
                $this->ppLeagueVerifyService->verifyAfterRound($ppRound['ppLeague_id'], $ppRound['round']);
            }
        }
        //CUP TODO
        //$ppcupGroup = getppcupGroupservice->getOne(id)
        //check if tournament needs more rounds 
        //if yes create
        //if no END tournament (if cupgroup -> )
    }

    public function isRoundFinished(array $ppRound) : bool {
        foreach ($ppRound['ppRoundMatches'] as $key => $ppRM) {
            if(!$ppRM['match']['verified_at']) return false;
        }
        return true;
    }
}
