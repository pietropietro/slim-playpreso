<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\Match;
use App\Service\Team;
use App\Service\PPRound;
use App\Service\ExternalAPI;


final class Elaborate extends BaseService{
    public function __construct(
        protected Match\Create $matchCreateService,
        protected Match\Verify $matchVerifyService,
        protected Match\Update $matchUpdateService,
        protected Match\Find $matchFindService,
        protected Team\Find $teamFindService,
        protected Team\Create $teamCreateService,
        protected PPRound\Update $ppRoundUpdateService,
    ) {}

    public function elaborateLsEvents(array $lsEvents, int $leagueId){       
        
        $created_counter = 0;
        $modified_counter = 0;
        $verified_counter = 0;

        foreach ($lsEvents as $key => $eventObj) {
            
            $modified_flag = false;

            $ls_id = (int) $eventObj->Eid;
            $round = isset($eventObj->ErnInf) ? (int) $eventObj->ErnInf : 1; 

            list($homeId, $awayId) = $this->checkCreateTeams($eventObj);
            if (!$homeId || !$awayId) {
                continue;
            }

            $externalDateStart = (string)$eventObj->Esd;
            $match = $this->matchFindService->getOne(
                id: $ls_id, 
                is_external_id: true, 
                withTeams: false, 
                withStats: false, 
                admin: true
            );
            if(!$match){
                $match = $this->checkLegacyMatch($eventObj->Eps, $ls_id, $leagueId, $round, $homeId, $awayId, $modified_flag);
            }
            if(!$match){
                $match = $this->matchCreateService->create($ls_id, $leagueId, $homeId, $awayId, $round, $externalDateStart);
                $created_counter ++;
                continue;
            }
            if(!$match || $match['verified_at']) continue;

            $this->checkUpdateTeams($match, $homeId, $awayId, $modified_flag);
            $this->checkUpdateTime($match, $externalDateStart, $modified_flag);
            if ($this->checkVerifyMatch($match['id'], $eventObj)){
                $verified_counter ++;
                continue;
            };
            $this->checkAnomalies($match, $eventObj, $modified_flag);

            if($modified_flag){
                $modified_counter ++;
            }
        }

        return array(
            "created" => $created_counter,
            "modified" => $modified_counter,
            "verified" => $verified_counter
        );
    }




    private function checkCreateTeams(object $eventObj){
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
        // Return IDs as an array
        return [$homeId, $awayId];
    }

    private function checkLegacyMatch(
        string $ls_eps,
        int $ls_id,
        int $leagueId,
        int $round,
        int $homeId,
        int $awayId,
        bool &$modified_flag
    ){
        if($ls_eps === 'NS'){
            $legacyMatchFuture = $this->matchFindService->getOneByLeagueRoundAndTeams(
                $leagueId, 
                $round, 
                $homeId, 
                $awayId
            );

            //UPDATE LEGACY
            if($legacyMatchFuture){
                $this->matchUpdateService->updateExternalId($legacyMatchFuture['id'], $ls_id);
                $modified_flag = true;
                return $legacyMatchFuture;
            }
        }
        else if($ls_eps === 'FT'){
            $dateOneMonthAgo = date('Y-m-d H:i:s', strtotime('-1 month'));

            $legacyMatchPast = $this->matchFindService->getOneByLeagueRoundAndTeams(
                $leagueId, 
                $round, 
                $homeId, 
                $awayId,
                " between '$dateOneMonthAgo' and now()"                    
            );

            if($legacyMatchPast && isset($legacyMatchPast['ls_id'])){
                $this->matchUpdateService->updateExternalId($legacyMatchPast['id'], $ls_id);
                $modified_flag = true;
                return $legacyMatchPast;
            }
        }
        return null;
    }


    private function checkUpdateTeams(array $match, int $homeId, int $awayId, bool &$modified_flag) {
        $teamsNeedUpdate = !$match['home_id'] 
                        || !$match['away_id']
                        || $match['home_id'] !== $homeId 
                        || $match['away_id'] !== $awayId;
    
        if ($teamsNeedUpdate) {
            $this->matchUpdateService->updateTeams($match['id'], $homeId, $awayId, false);
            $modified_flag = true;
        }
    }

    private function checkUpdateTime(array $match, string $externalDateStart, bool &$modified_flag): void {
        // Check if the match start time differs from the event start time
        if (new \DateTime($match['date_start']) != new \DateTime($externalDateStart)) {
            $this->matchUpdateService->updateDateStart($match['id'], $externalDateStart);
            $modified_flag = true;
        }
    }

    private function checkVerifyMatch(int $matchId, object $eventObj) {
        // Final score verification for full-time matches
        if ($eventObj->Eps === 'FT') {
            $this->matchVerifyService->verify($matchId, (int)$eventObj->Tr1, (int)$eventObj->Tr2);
            return true; // Skip further verification for this match
        }
    
        // Extra-time verification (using only 90' result)
        if (isset($eventObj->Tr1ET)) {
            $this->matchVerifyService->verify(
                $matchId,
                ((int)$eventObj->Tr1 - (int)$eventObj->Tr1ET),
                ((int)$eventObj->Tr2 - (int)$eventObj->Tr2ET),
                'et'
            );
            return true;
        }
    
        // Penalty verification (using only 90' result)
        if (isset($eventObj->Trp1)) {
            $this->matchVerifyService->verify($matchId, (int)$eventObj->Tr1, (int)$eventObj->Tr2, 'AP');
            return true;
        }
        return false;
    }

    private function checkAnomalies(array $match, object $eventObj, bool &$modified_flag){

        $penaltiesOrExtratime = in_array($eventObj->Eps, array('et', 'AP'));
        $matchFatalEvent = in_array($eventObj->Eps, array('Aband.', 'Postp.', 'Canc.' ));

        //if no interesting event OR already saved notes, return
        if((!$penaltiesOrExtratime && !$matchFatalEvent) || $match['notes'] == $eventObj->Eps) return;

        if($penaltiesOrExtratime){
            $this->matchUpdateService->updateNotes($match['id'], $eventObj->Eps);
            $modified_flag = true;
            return;
        }

        if($matchFatalEvent){
            $this->matchUpdateService->updateNotes($match['id'], $eventObj->Eps);
            // change match in all occurrances
            $this->ppRoundUpdateService->swapMatchId($match['id']);
            $modified_flag = true;
        }
    }

}
