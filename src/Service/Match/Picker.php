<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\League;
use App\Repository\MatchRepository;

final class Picker extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected League\Find $leagueService,
    ) {}
    
    public function pick(int $tournamentTypeId, int $howMany) : ?array{
        $matches = $this->nextMatchesForPPTournamentType($tournamentTypeId);
        $picked = array();

        for($i=0; $i<$howMany; $i++){
            $leagueOccurrances = array_count_values(
                array_column($matches, 'league_id')
            );

            if(count($leagueOccurrances) >= 2 && $picked){
                $ids=array_column($picked, 'league_id');
                $matches = array_filter($matches, 
                    fn ($m) => !in_array($m['league_id'], $ids)
                );
            }

            shuffle($matches);
            array_push($picked, array_pop($matches));
        }
        return $picked;
    }


    public function nextMatchesForPPTournamentType(int $tournamentTypeId){
        if(!$leagueIDs = $this->leagueService->getForPPTournamentType($tournamentTypeId, true)) return [];
        
        $matches = array();
        foreach ($leagueIDs as $id) {
            //limit to 10 matches in case of wrong round value (i.e. some league matches all round=1)
            if($retrieved = $this->matchRepository->getNextRoundForLeague($id, 10)){
                $matches = array_merge($matches, $retrieved);
            }
        }
        if(!$matches) return [];
        
        $reasonableMatches = array_filter($matches, 
            function ($e){
                return $e['date_start'] < date("Y-m-d H:i:s", strtotime('+8 days'));
            }
        );

        if(count($reasonableMatches) > 2) return $reasonableMatches;
        return $matches;
    }

}