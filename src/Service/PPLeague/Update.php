<?php

declare(strict_types=1);

namespace App\Service\PPLeague;

use App\Repository\PPLeagueRepository;
use App\Service\UserParticipation;
use App\Service\PPTournamentType;
use App\Service\Points;
use App\Service\BaseService;

final class Update  extends BaseService{
    public function __construct(
        protected PPLeagueRepository $ppLeagueRepository,
        protected UserParticipation\Find $userParticipationFindService,
        protected UserParticipation\Update $userParticipationUpdateService,
        protected PPTournamentType\Find $ppTournamentTypeFindService,
        protected PPTournamentType\Join $ppTournamentTypeJoinService,
        protected Points\Update $pointsUpdateService,
    ) {}

    public function setStarted(int $id){
        $this->ppLeagueRepository->setStarted($id);
    }
    
    public function afterFinished(int $id){
        $ppLeague = $this->ppLeagueRepository->getOne($id);
        $this->ppLeagueRepository->setFinished($id);
    
        $ups = $this->userParticipationFindService->getForTournament('ppLeague_id', $id);
    
        $ppTournamentType = $this->ppTournamentTypeFindService->getOne($ppLeague['ppTournamentType_id']);
        if($ppTournamentType['next'] && $ppTournamentType['promote']){
            $this->promote($ups, $ppTournamentType);
        }

        if($ppTournamentType['level'] > 1 && $ppTournamentType['relegate'] &&
            $previousPTT = $this->ppTournamentTypeFindService->getPreviousLevel($ppTournamentType['id'])
        ){
            $this->relegate($ups, $ppTournamentType['relegate'], $id, $previousPTT['id']);
        }
    }


    private function promote(array $ups, array $fromPPTT){
        //PROMOTE FIRST USERS
        for($i = 0; $i<$fromPPTT['promote'] ; $i++){
            $this->ppTournamentTypeJoinService->joinAvailable(
                $ups[$i]['user_id'], 
                $fromPPTT['next']['id'],
                pay: false
            );
        }

        if(!$fromPPTT['rejoin']) return;
        
        //REJOIN USERS
        $rejoinEndIndex = $fromPPTT['promote'] + $fromPPTT['rejoin'];
        for($i = $fromPPTT['promote']; $i < $rejoinEndIndex ;  $i++){
            $this->ppTournamentTypeJoinService->joinAvailable(
                $ups[$i]['user_id'], 
                $fromPPTT['id'],
                pay: false
            );
        }
    }

    private function relegate(
        array $ups, 
        int $howMany, 
        int $fromPPLeague_id, 
        int $relegatedToPPTournamentType_id
    ){

        for($i = (count($ups) - $howMany); $i < count($ups); $i++){
            // set earlier promotion up expired – EBR
            $this->userParticipationUpdateService->setEBR(
                $ups[$i]['user_id'], 
                $fromPPLeague_id,
                $relegatedToPPTournamentType_id
            );

            // rejoin relegated pptt
            $this->ppTournamentTypeJoinService->joinAvailable(
                $ups[$i]['user_id'], 
                $relegatedToPPTournamentType_id,
                pay: false
            );
        }
    }

}
