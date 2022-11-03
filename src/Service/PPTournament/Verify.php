<?php

declare(strict_types=1);

namespace App\Service\PPTournament;

use App\Service\BaseService;
use App\Service\PPTournamentType;
use App\Service\PPCupGroup;
use App\Service\PPCup;
use App\Service\PPRound;
use App\Service\UserParticipation;

final class Verify extends BaseService{
    public function __construct(
        protected PPTournamentType\Find $ppTournamentTypefindService,
        protected PPCupGroup\Find $ppCupGroupfindService,
        protected PPRound\Find $findPPRoundService,
        protected UserParticipation\Find $findUPService,
        protected PPRound\Create $createPPRoundService,
        protected UserParticipation\Update $updateUpService,
        protected PPLeague\Update $ppLeagueUpdateService,
        protected PPCupGroup\Update $ppCupGroupUpdateService,
        protected PPCup\Update $ppCupUpdateService,
    ) {}

    public function verifyAfterRound(string $tournamentColumn, int $tournamentId, int $round_just_finished){
        if(!in_array($tournamentColumn, array('ppLeague_id', 'ppCupGroup_id')))return;
        
        if($tournamentColumn === 'ppLeague_id'){
            $ppTournamentType = $this->ppTournamentTypefindService->getOneFromPPTournament('ppLeagues', $tournamentId);
            $tournamentRounds = $ppTournamentType['rounds'];
        }else{
            $ppTournament = $this->ppCupGroupfindService->getOne($tournamentId);
            $tournamentRounds = $ppTournament['rounds'];
        }

        if($tournamentRounds === $round_just_finished){
            $this->verifyAfterFinished($tournamentColumn, $tournamentId);
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

    private function verifyAfterFinished(string $tournamentColumn, int $tournamentId){
        if(!in_array($tournamentColumn, array('ppLeague_id', 'ppCupGroup_id')))return;

        if($tournamentColumn === 'ppLeague_id'){
            $this->ppLeagueUpdateService->setFinished($tournamentId);
        }else{
            //TODO CHECK IF ALSO FINISH CUP
            $this->ppCupGroupUpdateService->setFinished($tournamentId);
        }
        $this->updateUpService->setFinished($tournamentColumn, $tournamentId);

        //TODO handle ups, best 3 users get promoted to next level
        //TODO handle trophies ?
    }

    public function verifyAfterUserJoined(string $tournamentColumn, int $tournamentId, int $tournamentTypeId){
        if(!in_array($tournamentColumn, array('ppLeague_id', 'ppCupGroup_id')))return;

        $participantsCount = $this->findUPService->countInTournament($tournamentColumn, $tournamentId);
        
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
