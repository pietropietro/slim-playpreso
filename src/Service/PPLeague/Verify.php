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
        protected UserParticipation\Update $updateUpService,
    ) {}

    private function verify(int $id, int $round_finished){
        $ppLeague = $this->findService->getOne($id);
        if($ppleague['ppTournamentType']['rounds'] > $round_finished){
            $this->createPPRoundService->create('ppLeague_id', $id, $round_finished + 1);
            $this->ppLeagueRepository->incRoundCount($id);
            return;
        }
        if($ppleague['ppTournamentType']['rounds'] === $round_finished){
            $this->ppLeagueRepository->setFinished($id);
            $this->updateUpService->setFinished('ppLeague_id', $id);
        }
    }

}
