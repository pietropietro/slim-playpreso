<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\PPRound;
use App\Repository\MatchRepository;
use App\Service\Match;


final class Elaborate extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected PPRound\Verify $ppRoundVerifyService,
        protected Match\Create $matchCreateService,
        protected Match\Verify $matchVerifyService,
    ) {}

    public function elaborateLsEvents(array $lsEvents, int $league_id){
        $counts = ["created" => 0, "rescheduled" => 0];
        
        $match_verified_ids = [];
        foreach ($lsEvents as $key => $eventObj) {
            $match = $this->matchRepository->getOne((int) $eventObj->Eid, true);
            
            if(!$match && $eventObj->Eps === 'FT') continue;
            
            if(!$match){
                $this->matchCreateService->create($eventObj, $league_id);
                $counts["created"]++;
                continue;
            }
           
            if($match['verified_at'])continue;
            
            if($eventObj->Eps === 'FT'){
                $this->matchVerifyService->verify($eventObj, $match['id']);
                array_push($match_verified_ids, $match['id']);
                continue;
            }

            if(new \DateTime($match['date_start']) != new \DateTime((string)$eventObj->Esd)){
                $this->matchRepository->updateDateStart($match['id'], (string)$eventObj->Esd);
                $counts['rescheduled']++;
            }   
        }

        $counts['verified'] = count($match_verified_ids);
        if($counts['verified'] > 0){
            $this->ppRoundVerifyService->verify($match_verified_ids);
        }

        return $counts;
    }

}