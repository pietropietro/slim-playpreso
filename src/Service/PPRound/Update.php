<?php

declare(strict_types=1);

namespace App\Service\PPRound;

use App\Service\BaseService;
use App\Service\PPRoundMatch;
use App\Service\Match;
use App\Repository\PPRoundRepository;

final class Update extends BaseService{
    public function __construct(
        protected PPRoundMatch\Update $ppRoundMatchUpdateService,
        protected Match\Picker $matchPickerService,
        protected PPRoundRepository $ppRoundRepository,
    ){}

    public function swapMatchId(int $matchId){
        $pprms = $this->ppRoundRepository->getPPRoundMatchesForMatchId($matchId);
        foreach ($pprms as $pprm) {
            $newMatch = $this->matchPickerService->pick($pprm['ppTournamentType_id'], 1)[0] ?? null;
            
            if(!$newMatch){
                error_log('could not change old_match_id:'.$matchId.', for pptt_id:'.$pprm['ppTournamentType_id']);
                return;
            }

            $this->ppRoundMatchUpdateService->swap($pprm['ppRoundMatch_id'], $newMatch['id']);
        }
    }
}
