<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\Guess;
use App\Service\PPRound;
use App\Repository\MatchRepository;
use App\Repository\TeamRepository;

final class Elaborate extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected TeamRepository $teamRepository,
        protected Guess\Verify $guessVerifyService,
        protected PPRound\Verify $ppRoundVerifyService,
    ) {}

    public function elaborateLsEvents(array $lsEvents, int $league_id){
        $counts = ["created" => 0, "verified" => 0 , "rescheduled" => 0];
        
        foreach ($lsEvents as $key => $eventObj) {
            $match = $this->matchRepository->getOne((int) $eventObj->Eid, true);
            
            if(!$match && $eventObj->Eps === 'FT') continue;
            
            if(!$match){
                $this->create($eventObj, $league_id);
                $counts["created"]++;
                continue;
            }
           
            if($match['verified_at'])continue;
            
            if($eventObj->Eps === 'FT'){
                $this->verify($eventObj, $match['id']);
                $counts['verified']++;
                continue;
            }

            if(new \DateTime($match['date_start']) != new \DateTime((string)$eventObj->Esd)){
                $this->matchRepository->updateDateStart($match['id'], $eventObj->Esd);
                $counts['rescheduled']++;
            }   
        }
        return $counts;
    }

    private function create(Object $eventObj, int $league_id){
        $home_id = $this->teamRepository->idFromExternal((int)$eventObj->T1[0]->ID);
        $away_id = $this->teamRepository->idFromExternal((int)$eventObj->T2[0]->ID);
        $round = (int)$eventObj->Ern;
        $this->matchRepository->create((int)$eventObj->Eid, $league_id, $home_id, $away_id, $round, (string)$eventObj->Esd);
    }

    private function verify(Object $eventObj, int $matchId){
        $this->matchRepository->verify($matchId, (int)$eventObj->Tr1, (int)$eventObj->Tr2);
        $this->guessVerifyService->verify($matchId, (int)$eventObj->Tr1, (int)$eventObj->Tr2);
        // $this->ppRoundVerifyService->verify($matchId);
        //tournamentService-> check started, finished, finished round
    }

}