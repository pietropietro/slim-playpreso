<?php

declare(strict_types=1);

namespace App\Service\PPLeague;

use App\Service\BaseService;
use App\Service\PPLeague;
use App\Service\PPRound;
use App\Service\UserParticipation;
use App\Repository\PPLeagueRepository;

final class Verify extends BaseService{
    public function __construct(
        protected PPLeagueRepository $ppLeagueRepository,
        protected PPLeague\Find $findService,
        protected PPRound\Create $createPPRoundService,
        protected PPRound\Find $findPPRoundService,
        protected UserParticipation\Update $updateUpService,
    ) {}

    public function verify(int $id, int $round_just_finished){
        $ppLeague = $this->findService->getOne($id);
        if($ppLeague['ppTournamentType']['rounds'] === $round_just_finished){
            $this->ppLeagueRepository->setFinished($id);
            $this->updateUpService->setFinished('ppLeague_id', $id);
        }
        
        //double-check that no successive round was already created
        //this prevents double round creation when recalculating a round.
        $nextRound = $round_just_finished + 1;
        if($this->findPPRoundService->has('ppLeague_id', $id, $nextRound))return;

        if($ppLeague['ppTournamentType']['rounds'] > $round_just_finished){
            $this->createPPRoundService->create(
                'ppLeague_id', 
                $id, 
                $ppLeague['ppTournamentType_id'], 
                $nextRound
            );
            
            //TODO DELETE counter
            $this->ppLeagueRepository->incRoundCount($id);
            return;
        }
        
    }

}
