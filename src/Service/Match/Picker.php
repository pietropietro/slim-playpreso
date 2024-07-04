<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\League;
use App\Repository\MatchRepository;

final class Picker extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected League\Find $leagueFindService,
    ) {}

    public function pickForToday(){
        return $this->matchRepository->pickForToday();
    }
    
    public function pick(int $tournamentTypeId, int $howMany) : ?array{
        $matches = $this->nextMatchesForPPTournamentType($tournamentTypeId);
        $picked = array();

        for($i=0; $i<$howMany; $i++){
            if(!$matches)continue;
            $leagueOccurrances = array_count_values(
                array_column($matches, 'league_id')
            );

            if(count($leagueOccurrances) > 2 && $picked){
                $ids=array_column($picked, 'league_id');
                $matches = array_filter($matches, 
                    fn ($m) => !in_array($m['league_id'], $ids)
                );
            }else if(count($leagueOccurrances) == 2 && count($picked)>1){
                $matches = array_filter($matches, 
                    fn ($m) => !in_array($m['league_id'], array($picked[0]['league_id']))
                );
            }

            shuffle($matches);
            array_push($picked, array_pop($matches));
        }
        return $picked;
    }


    public function nextMatchesForPPTournamentType(int $tournamentTypeId){
        if(!$leagues = $this->leagueFindService->getForPPTournamentType($tournamentTypeId)) return [];
        
        $leagueIds = array_column($leagues, 'id');
        
        //exclude leagues with suspect team names 
        //(i.e. 'manchester/chelsea' or 'group a winner'  which still need data);
        $suspectLeagues =  $this->leagueFindService->getSuspectTeamNameLeagues();
        $suspectLeagueIds = array_column($suspectLeagues, 'id');
        $leagueIds = array_diff($leagueIds, $suspectLeagueIds);

        
        $matches = array();
        foreach ($leagueIds as $id) {
            if($retrieved = $this->nextRoundForLeague($id)){
                $matches = array_merge($matches, $retrieved);
            }
        }
        if(!$matches || $matches < 3){
            $matches = array();
            foreach ($leagueIds as $id) {
                if($retrieved = $this->nextMatchesForLeague($id)){
                    $matches = array_merge($matches, $retrieved);
                }
            }
        }

        // Group matches by parent_id
        $groupedMatches = [];
        foreach ($matches as $match) {
            $parentId = $match['league_parent_id'];
            if (!isset($groupedMatches[$parentId])) {
                $groupedMatches[$parentId] = [];
            }
            $groupedMatches[$parentId][] = $match;
        }

        // Filter matches to keep only the highest round for each group
        $filteredMatches = [];
        foreach ($groupedMatches as $parentId => $group) {
            usort($group, function ($a, $b) {
                return $b['round'] - $a['round']; // Sort by round in descending order
            });
            $highestRound = $group[0]['round'];
            $filteredMatches = array_merge($filteredMatches, array_filter($group, function ($match) use ($highestRound) {
                return $match['round'] == $highestRound;
            }));
        }
        
        $reasonableMatches = array_filter($filteredMatches, 
            function ($e){
                return $e['date_start'] < date("Y-m-d H:i:s", strtotime('+8 days'));
            }
        );

        if(count($reasonableMatches) > 2) return $reasonableMatches;
        if(count($filteredMatches) > 2) return $filteredMatches;
        return $matches;
    }

    private function nextMatchesForLeague(int $leagueId){
        //to easily solve the no round sequence
        return $this->matchRepository->nextMatches($leagueId, 10);
    }

    private function nextRoundForLeague(int $leagueId){
        //limit to 10 matches in case of wrong round value (i.e. some league matches all round=1)
        return $this->matchRepository->getNextRoundForLeague($leagueId, 10);
    }

}