<?php

declare(strict_types=1);

namespace App\Service\PPTournament;

use App\Service\BaseService;
use App\Service\PPTournamentType;
use App\Service\PPCupGroup;
use App\Service\PPLeague;
use App\Service\PPRound;
use App\Service\UserParticipation;

final class VerifyAfterRound extends BaseService{
    public function __construct(
        protected PPTournamentType\Find $ppTournamentTypefindService,
        protected PPCupGroup\Find $ppCupGroupfindService,
        protected PPRound\Find $findPPRoundService,
        protected PPRound\Create $createPPRoundService,
        protected UserParticipation\Update $updateUpService,
        protected PPLeague\Update $ppLeagueUpdateService,
        protected PPCupGroup\Update $ppCupGroupUpdateService,
    ) {}

    public function afterRound(string $tournamentColumn, int $tournamentId, int $round_just_finished){
        if(!in_array($tournamentColumn, array('ppLeague_id', 'ppCupGroup_id')))return;
        
        if($tournamentColumn === 'ppLeague_id'){
            $ppTournamentType = $this->ppTournamentTypefindService->getOneFromPPTournament('ppLeagues', $tournamentId);
            $tournamentRounds = $ppTournamentType['rounds'];
        }else{
            $ppTournament = $this->ppCupGroupfindService->getOne($tournamentId);
            $tournamentRounds = $ppTournament['rounds'];
        }

        if($tournamentRounds === $round_just_finished){
            $this->afterFinished($tournamentColumn, $tournamentId);
        }
        
        //prevent double round creation when recalculating a round.
        $nextRound = $round_just_finished + 1;
        //move check in create ppround service
        if($this->findPPRoundService->has($tournamentColumn, $tournamentId, $nextRound))return;

        if($tournamentRounds > $round_just_finished){
            $this->createPPRoundService->create(
                $tournamentColumn, 
                $tournamentId, 
                $ppTournamentType['id'] ?? $ppTournament['ppTournamentType_id'], 
                $nextRound
            );
            return;
        }
    }

    private function afterFinished(string $tournamentColumn, int $tournamentId){
        if(!in_array($tournamentColumn, array('ppLeague_id', 'ppCupGroup_id')))return;

        $this->updateUpService->setFinished($tournamentColumn, $tournamentId);

        if($tournamentColumn === 'ppLeague_id'){
            $this->ppLeagueUpdateService->setFinished($tournamentId);
            return;
        }
        
        $this->ppCupGroupUpdateService->setFinished($tournamentId);
    }

}
