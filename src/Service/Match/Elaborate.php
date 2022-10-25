<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Repository\MatchRepository;
use App\Service\Match;


final class Elaborate extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected Match\Create $matchCreateService,
        protected Match\Verify $matchVerifyService,
    ) {}

    public function elaborateLsEvents(array $lsEvents, int $league_id){        
        foreach ($lsEvents as $key => $eventObj) {
            $match = $this->matchRepository->getOne((int) $eventObj->Eid, true);
            
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

            if(new \DateTime($match['date_start']) != new \DateTime((string)$eventObj->Esd)){
                $this->matchRepository->updateDateStart($match['id'], (string)$eventObj->Esd);
            }   
        }
    }
}
