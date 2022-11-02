<?php

declare(strict_types=1);

namespace App\Service\PPRound;

use App\Service\BaseService;
use App\Service\PPTournament;
use App\Service\UserParticipation;
use App\Service\PPRound;


final class Verify extends BaseService{
    public function __construct(
        protected PPRound\Find $findService,
        protected PPTournament\Verify $ppTournamentVerifyService,
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
        $tournamentColumn = $ppRound['ppLeague_id'] ? 'ppLeague_id' : 'ppCupGroup_id';
        $tournamentId = $ppRound['ppLeague_id'] ?? $ppRound['ppCupGroup_id'];

        $this->updateUpService->update('ppLeague_id', $ppRound['ppLeague_id']);

        if($this->isPPRoundFinished($ppRound)){
            $this->ppTournamentVerifyService->verifyAfterRound($tournamentColumn, $tournamentId, $ppRound['round']);
        }
    }

    public function isPPRoundFinished(array $ppRound) : bool {
        foreach ($ppRound['ppRoundMatches'] as $key => $ppRM) {
            if(!$ppRM['match']['verified_at']) return false;
        }
        return true;
    }
}
