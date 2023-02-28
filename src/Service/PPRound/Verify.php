<?php

declare(strict_types=1);

namespace App\Service\PPRound;

use App\Service\BaseService;
use App\Service\PPTournament;
use App\Service\UserParticipation;
use App\Service\PPRound;


final class Verify extends BaseService{
    public function __construct(
        protected PPRound\Find $ppRoundFindService,
        protected PPTournament\VerifyAfterRound $verify,
        protected UserParticipation\Update $updateUpService
    ){}
    
    public function verifyAfterMatch(int $matchId){
        $ppRoundIds = $this->ppRoundFindService->getForMatches(array($matchId), ids_only: true);
        foreach ($ppRoundIds as $key => $id) {   
            if(isset($id) && (int)$id){
                $this->verify($id);
            }
        }
    }

    public function verify($id){
        $ppRound=$this->ppRoundFindService->getOne($id);
        $tournamentColumn = $ppRound['ppLeague_id'] ? 'ppLeague_id' : 'ppCupGroup_id';
        $tournamentId = $ppRound['ppLeague_id'] ?? $ppRound['ppCupGroup_id'];

        $this->updateUpService->update($tournamentColumn, $tournamentId);

        if($this->isPPRoundFinished($ppRound)){
            $this->verify->afterRound($tournamentColumn, $tournamentId, $ppRound['round']);
        }
    }

    public function isPPRoundFinished(array $ppRound) : bool {
        foreach ($ppRound['ppRoundMatches'] as $key => $ppRM) {
            if(!$ppRM['match']['verified_at']) return false;
        }
        return true;
    }
}
