<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\Match;
use App\Service\Team;
use App\Service\ExternalAPI;


final class Elaborate extends BaseService{
    public function __construct(
        protected Match\Create $matchCreateService,
        protected Match\Verify $matchVerifyService,
        protected Match\Update $matchUpdateService,
        protected Match\Find $matchFindService,
        protected Team\Find $teamFindService,
        protected Team\Create $teamCreateService,
        protected ExternalAPI\ImportTeamLogo $importLogoService,
    ) {}

    public function elaborateLsEvents(array $lsEvents, int $leagueId){       
        
        $created_counter = 0;
        $modified_counter = 0;
        $verified_counter = 0;

        foreach ($lsEvents as $key => $eventObj) {
            
            $modified_flag = false;

            $ls_id = (int) $eventObj->Eid;
            $round = isset($eventObj->ErnInf) ? (int) $eventObj->ErnInf : 1; 

            //TEAM CREATION IF NEEDED
            $homeId = $this->teamFindService->idFromExternal((int)$eventObj->T1[0]->ID);
            if(!$homeId){
                $homeId = $this->teamCreateService->create(
                    ls_id: (int)$eventObj->T1[0]->ID,
                    name: $eventObj->T1[0]->Nm,
                    country: $eventObj->T1[0]->CoNm ?? "test"
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

            //TEAMS LOGO IMPORT IF MISSING
            if(isset($eventObj->T1[0]->Img))$this->checkLogo($homeId, $eventObj->T1[0]->Img);
            if(isset($eventObj->T2[0]->Img))$this->checkLogo($awayId, $eventObj->T2[0]->Img);
            
            $dateStart = (string)$eventObj->Esd;

            //RETRIEVE MATCH FROM DB IF EXISTS
            $match = $this->matchFindService->getOne($ls_id, true, false, false);

            //CREATE MATCH OR UPDATE LEGACY(i.e. wrong ls_id)
            if(!$match && $eventObj->Eps === 'NS'){
                //UPDATE LEGACY
                if($homeId && $awayId && $legacyMatch = $this->matchFindService->getOneByLeagueRoundAndTeams($leagueId, $round, $homeId, $awayId)){
                    $this->matchUpdateService->updateExternalId($legacyMatch['id'], $ls_id);
                    $modified_flag = true;
                    continue;
                }
                //CREATE
                if($this->matchCreateService->create($ls_id, $leagueId, $homeId, $awayId, $round, $dateStart)){
                    $created_counter ++;
                }
                continue;
            }

            if(!$match) continue;
            if($match['verified_at'])continue;

            //UPDATE TEAMS
            if(!$match['home_id'] || !$match['away_id']
                || $match['home_id'] != $homeId || $match['away_id'] != $awayId
            ){
                $this->matchUpdateService->updateTeams($match['id'], $homeId, $awayId, false);
                $modified_flag = true;
            }

            //UPDATE TIME
            if(new \DateTime($match['date_start']) != new \DateTime($dateStart)){
                $this->matchUpdateService->updateDateStart($match['id'], $dateStart);
                $modified_flag = true;
            }
            

            //VERIFY MATCH - i.e. final score 
            if($eventObj->Eps === 'FT'){
                $this->matchVerifyService->verify($match['id'], (int)$eventObj->Tr1, (int)$eventObj->Tr2);
                $verified_counter ++;
                continue;
            }

            // NO EXTRATIME
            if(isset($eventObj->Tr1ET)){
                $this->matchVerifyService->verify($match['id'], (int)$eventObj->Tr1OR, (int)$eventObj->Tr2OR, 'et');
                $verified_counter ++;
                continue;
            }

            // NO PENALTIES
            if(isset($eventObj->Trp1)){
                $this->matchVerifyService->verify($match['id'], (int)$eventObj->Tr1, (int)$eventObj->Tr2, 'AP');
                $verified_counter ++;
                continue;
            }

            //Match post/cancel./aband
            if( in_array($eventObj->Eps, array('Aband.', 'Postp.', 'Canc.' , 'et', 'AP')) &&
                (!isset($match['notes']) || $match['notes'] != $eventObj->Eps)
            ){
                $this->matchUpdateService->updateNotes($match['id'], $eventObj->Eps);
                $modified_flag = true;
            }

            if($modified_flag){
                $modified_counter ++;
            }
        }
        return array(
            "created" => $created_counter,
            "modified" => $modified_counter,
            "verified" =>$verified_counter
        );
    }

    private function checkLogo(int $id, string $external_logo_suffix){
        $path = $_ENV['STATIC_IMAGE_FOLDER'] . 'teams/' . $id . '.png';;    
        if (!file_exists($path)) {
            $this->importLogoService->fetch(
                $external_logo_suffix, 
                $id
            );
        }
    }
}
