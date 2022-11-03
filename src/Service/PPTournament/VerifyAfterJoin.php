<?php

declare(strict_types=1);

namespace App\Service\PPTournament;

use App\Service\BaseService;
use App\Service\PPTournamentType;
use App\Service\PPCupGroup;
use App\Service\PPLeague;
use App\Service\PPCup;
use App\Service\PPRound;
use App\Service\UserParticipation;

final class VerifyAfterJoin extends BaseService{
    public function __construct(
        protected UserParticipation\Find $findUpService,
        protected PPTournamentType\Find $ppTournamentTypefindService,
        protected PPCupGroup\Find $ppCupGroupfindService,
        protected PPLeague\Update $ppLeagueUpdateService,
        protected PPRound\Create $createPPRoundService,
        protected PPCup\Update $ppCupUpdateService,
    ) {}
    
    public function afterJoined(string $tournamentColumn, int $tournamentId, int $tournamentTypeId){
        if(!in_array($tournamentColumn, array('ppLeague_id', 'ppCupGroup_id')))return;

        $participantsCount = $this->findUpService->countInTournament($tournamentColumn, $tournamentId);
        
        //TODO add 'participants' column in ppleague to have same way to access value as ppcupgroups
        $maxParticipants =  $tournamentColumn === 'ppLeague_id' ? 
            $this->ppTournamentTypefindService->getOneFromPPTournament('ppLeagues', $tournamentId)['participants'] :
            $ppTournament = $this->ppCupGroupfindService->getOne($tournamentId)['participants'];

        if($participantsCount && $participantsCount === $maxParticipants){
            $this->startPPTournament($tournamentColumn, $tournamentId, $tournamentTypeId);
        }

    }

    private function startPPTournament(string $tournamentColumn, int $tournamentId, int $tournamentTypeId){
        if(!in_array($tournamentColumn, array('ppLeague_id', 'ppCupGroup_id')))return;
        
        if($tournamentColumn === 'ppLeague_id'){
            $this->ppLeagueUpdateService->setStarted($tournamentId);
            $this->createPPRoundService->create($tournamentColumn, $tournamentId, $tournamentTypeId, 1);
            return;
        }

        if((bool)$this->ppCupGroupfindService->getJoinable($tournamentId)) return;
        $ppCupGroup = $this->ppCupGroupfindService->getOne($tournamentId);
        $this->ppCupUpdateService->start($ppCupGroup['ppCup_id']);
    }
}