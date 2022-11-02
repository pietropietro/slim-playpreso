<?php

declare(strict_types=1);

namespace App\Service\PPTournament;

use App\Service\BaseService;
use App\Service\PPLeague;
use App\Service\PPCupGroup;
use App\Service\PPRound;
use App\Service\UserParticipation;

final class Verify extends BaseService{
    public function __construct(
        protected PPLeague\Find $ppLeaguefindService,
        protected PPCupGroup\Find $ppCupGroupfindService,
        protected PPRound\Find $findPPRoundService,
        protected PPRound\Create $createPPRoundService,
        protected UserParticipation\Update $updateUpService,
        protected PPLeague\Update $ppLeagueUpdateService,
        protected PPCupGroup\Update $ppCupGroupUpdateService,
    ) {}

    public function verifyAfterRound(string $tournamentColumn, int $tournamentId, int $round_just_finished){
        if($tournamentColumn === 'ppLeague_id'){
            $ppTournament = $this->ppLeaguefindService->getOne($tournamentId);
            $tournamentRounds = $ppTournament['ppTournamentType']['rounds'];
        }else{
            $ppTournament = $this->ppCupGroupfindService->getOne($tournamentId);
            $tournamentRounds = $ppTournament['rounds'];
        }

        if($tournamentRounds === $round_just_finished){
            $this->verifyAfterFinished($tournamentColumn, $tournamentId);
        }
        
        //prevent double round creation when recalculating a round.
        $nextRound = $round_just_finished + 1;
        if($this->findPPRoundService->has($tournamentColumn, $tournamentId, $nextRound))return;

        if($tournamentRounds > $round_just_finished){
            $this->createPPRoundService->create(
                $tournamentColumn, 
                $tournamentId, 
                $ppTournament['ppTournamentType_id'], 
                $nextRound
            );
            return;
        }
    }

    public function verifyAfterUserJoined(string $tournamentColumn, int $tournamentId){
        //TODO!!
        if($ppTournamentType['participants'] === count($this->findUPservice->getForTournament($column, $ppTournament['id']))){
            //TODO startppcupservice
            $started = $ppTournamentType['cup_format'] ? null 
            : $this->startPPLeagueService->start($ppTournament['id'], $ppTournamentType['id']);
        }
    }

    private function verifyAfterFinished(string $tournamentColumn, int $tournamentId){
       
        if($tournamentColumn === 'ppLeague_id'){
            $this->ppLeagueUpdateService->setFinished($tournamentId);
        }else{
            $this->ppCupGroupUpdateService->setFinished($tournamentId);
        }
        $this->updateUpService->setFinished($tournamentColumn, $tournamentId);

        //TODO handle ups, best 3 users get promoted to next level
        //TODO handle trophies ?
    }
    

}
