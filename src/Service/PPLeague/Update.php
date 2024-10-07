<?php

declare(strict_types=1);

namespace App\Service\PPLeague;

use App\Repository\PPLeagueRepository;
use App\Service\UserParticipation;
use App\Service\PPTournamentType;
use App\Service\Points;
use App\Service\BaseService;
use App\Service\UserNotification;


final class Update  extends BaseService{
    public function __construct(
        protected PPLeagueRepository $ppLeagueRepository,
        protected UserParticipation\Find $userParticipationFindService,
        protected UserParticipation\Update $userParticipationUpdateService,
        protected PPTournamentType\Find $ppTournamentTypeFindService,
        protected PPTournamentType\Join $ppTournamentTypeJoinService,
        protected Points\Update $pointsUpdateService,
        protected UserNotification\Create $userNotificationCreateService
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
        if($ppTournamentType['rejoin']){
            $this->rejoin($ups, $ppTournamentType);
        };
        if($ppTournamentType['level'] > 1 && $ppTournamentType['relegate'] &&
            $previousPTT = $this->ppTournamentTypeFindService->getPreviousLevel($ppTournamentType['id'])
        ){
            $this->relegate($ups, $ppTournamentType['relegate'], $id, $previousPTT['id']);
        }

        $this->sendNotifications($ups, $ppTournamentType);
    }

    private function sendNotifications(array $ups, array $ppTournamentType){
        foreach ($ups as $up) {
            $title = $ppTournamentType['emoji'].' '.$ppTournamentType['name'].' '.$ppTournamentType['level'].' is over';
            if($up['position']==1){
                $body = "YOU ARE THE WINNER!";
            }else if(in_array($up['position'], [2,22,32,42,52,62,72,82,92])){
                $body = "You arrived ".$up['position']."nd";
            }else if(in_array($up['position'], [3,23,33,43,53,63,73,83,93])){
                $body = "You arrived ".$up['position']."rd";
            }else{
                $body = "You arrived ".$up['position']."th";
            }

            $notificationText = array(
                'title' => $title,
                'body' => $body
            );
            
            $this->userNotificationCreateService->create(
                $up['user_id'],
                'ppleague_finished',
                $up['id'], 
                $notificationText
            );
        }        
    }


    private function promote(array $ups, array $fromPPTT){
        for($i = 0; $i<$fromPPTT['promote'] ; $i++){
            $this->ppTournamentTypeJoinService->joinAvailable(
                $ups[$i]['user_id'], 
                $fromPPTT['next']['id'],
                pay: false
            );
        }
    }

    private function rejoin(array $ups, array $ppTT){
        $rejoinEndIndex = $ppTT['promote'] + $ppTT['rejoin'];
        for($i = $ppTT['promote']; $i < $rejoinEndIndex ;  $i++){
            $this->ppTournamentTypeJoinService->joinAvailable(
                $ups[$i]['user_id'], 
                $ppTT['id'],
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
