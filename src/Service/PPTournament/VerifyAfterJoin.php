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
        protected UserParticipation\Update $updateUpService,
        protected PPTournamentType\Find $ppTournamentTypefindService,
        protected PPCupGroup\Find $ppCupGroupfindService,
        protected PPLeague\Update $ppLeagueUpdateService,
        protected PPRound\Create $createPPRoundService,
        protected PPCup\Update $ppCupUpdateService,
    ) {}
    
    public function afterJoined(string $tournamentColumn, int $tournamentId, int $tournamentTypeId){
        if(!in_array($tournamentColumn, array('ppLeague_id', 'ppCupGroup_id')))return;

        $participantsCount = $this->findUpService->countInTournament($tournamentColumn, $tournamentId);

        //TODO add 'participants' column in ppleague to have same way to access maxParticipants as ppcupgroups
        if($tournamentColumn === 'ppCupGroup_id'){
            //TODO if ppcupgroup udpate ups to set total_cup_points and sort ups before group start
            $this->updateUpService->update($tournamentColumn, $tournamentId);
            $maxParticipants = $this->ppCupGroupfindService->getOne($tournamentId)['participants'];
        }else{
            $maxParticipants = $this->ppTournamentTypefindService->getOneFromPPTournament('ppLeagues', $tournamentId)['participants'];
        }
        

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

        $startingGroup = $this->ppCupGroupfindService->getOne($tournamentId);
        if((bool)$this->ppCupGroupfindService->getNotFull(ppCupId: $startingGroup['ppCup_id'], level:$startingGroup['level'])) return;        
        $this->ppCupUpdateService->start($startingGroup['ppCup_id'], level: $startingGroup['level']);
    }
}