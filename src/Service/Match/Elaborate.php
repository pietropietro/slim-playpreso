<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\Match;
use App\Service\Team;


final class Elaborate extends BaseService{
    public function __construct(
        protected Match\Create $matchCreateService,
        protected Match\Verify $matchVerifyService,
        protected Match\Update $matchUpdateService,
        protected Match\Find $matchFindService,
        protected Team\Find $teamFindService,
        protected Team\Create $teamCreateService,
    ) {}

    public function elaborateLsEvents(array $lsEvents, int $leagueId){        
        foreach ($lsEvents as $key => $eventObj) {
            
            $ls_id = (int) $eventObj->Eid;
            $round = (int) $eventObj->ErnInf; 
            $homeId = $this->teamFindService->idFromExternal((int)$eventObj->T1[0]->ID);
            if(!$homeId){
                $homeId = $this->teamCreateService->create(
                    ls_id: (int)$eventObj->T1[0]->ID,
                    name: $eventObj->T1[0]->Nm,
                    country: $eventObj->T1[0]->CoNm
                );
            }
            $awayId = $this->teamFindService->idFromExternal((int)$eventObj->T2[0]->ID);
            if(!$awayId){
                $awayId = $this->teamCreateService->create(
                    ls_id: (int)$eventObj->T2[0]->ID,
                    name: $eventObj->T2[0]->Nm,
                    country: $eventObj->T2[0]->CoNm
                );
            }
            $dateStart = (string)$eventObj->Esd;

            //RETRIEVE MATCH FROM DB IF EXISTS
            $match = $this->matchFindService->getOne($ls_id, true, false, false);

            //UPDATE LEGACY(i.e. wrong ls_id) OR CREATE
            if(!$match && $eventObj->Eps === 'NS'){
                //UPDATE LEGACY
                if($homeId && $awayId && $legacyMatch = $this->matchFindService->getOneByLeagueRoundAndTeams($leagueId, $round, $homeId, $awayId)){
                    $this->matchUpdateService->updateExternalId($legacyMatch['id'], $ls_id);
                    continue;
                }
                //CREATE
                $this->matchCreateService->create($ls_id, $leagueId, $homeId, $awayId, $round, $dateStart);
                continue;
            }

            if(!$match) continue;
            if($match['verified_at'])continue;

            //UPDATE TEAMS
            if(!$match['home_id'] || !$match['away_id']){
                $this->matchUpdateService->updateTeams($match['id'], $homeId, $awayId, false);
            }

            //UPDATE TIME
            if(new \DateTime($match['date_start']) != new \DateTime($dateStart)){
                $this->matchUpdateService->updateDateStart($match['id'], $dateStart);
            }
            

            //VERIFY MATCH - i.e. final score 
            if($eventObj->Eps === 'FT'){
                $this->matchVerifyService->verify($match['id'], (int)$eventObj->Tr1, (int)$eventObj->Tr2);
                continue;
            }

            // NO EXTRATIME
            if(isset($eventObj->Tr1ET)){
                $this->matchVerifyService->verify($match['id'], (int)$eventObj->Tr1OR, (int)$eventObj->Tr2OR, 'et');
                continue;
            }

            //Match post/cancel./aband
            if( in_array($eventObj->Eps, array('Aband.', 'Postp.', 'Canc.' , 'et')) &&
                (!isset($match['notes']) || $match['notes'] != $eventObj->Eps)
            ){
                $this->matchUpdateService->updateNotes($match['id'], $eventObj->Eps);
            }
               
        }
    }
}
