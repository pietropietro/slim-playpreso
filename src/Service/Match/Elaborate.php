<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\Match;


final class Elaborate extends BaseService{
    public function __construct(
        protected Match\Create $matchCreateService,
        protected Match\Verify $matchVerifyService,
        protected Match\Update $matchUpdateService,
        protected Match\Find $matchFindService,
    ) {}

    public function elaborateLsEvents(array $lsEvents, int $league_id){        
        foreach ($lsEvents as $key => $eventObj) {
            $match = $this->matchFindService->getOne((int) $eventObj->Eid, true, false);
            
            if(!$match && $eventObj->Eps === 'FT') continue;
            
            if(!$match){
                $this->matchCreateService->create($eventObj, $league_id);
                continue;
            }
           
            if($match['verified_at'])continue;
            
            if($eventObj->Eps === 'FT'){
                $this->matchVerifyService->verify($match['id'], (int)$eventObj->Tr1, (int)$eventObj->Tr2);
                continue;
            }

            // TODO if abandoned change ppRoundMatches
            // if($eventObj->Eps === 'Aband.'){
                
            // }
            //TODO postponed handle
            // if($eventObj->Eps === 'Postp.'){
                
            // }
            
            if(!$match['home_id'] || $match['away_id']){
                $this->matchUpdateService->updateTeams($match['id'],(int)$eventObj->T1[0]->ID, (int)$eventObj->T2[0]->ID, true);
            }
            if(new \DateTime($match['date_start']) != new \DateTime((string)$eventObj->Esd)){
                $this->matchUpdateService->updateDateStart($match['id'], (string)$eventObj->Esd);
            }   
        }
    }
}
