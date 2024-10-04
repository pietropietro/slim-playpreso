<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\League;
use App\Repository\MatchRepository;
use App\Repository\PPTournamentTypeRepository;

final class Picker extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected League\Find $leagueFindService,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
    ) {}

    public function pickForToday(){
        return $this->matchRepository->pickForToday();
    }
    
    public function pick(int $ppttId, int $howMany) : ?array{
        $matches = $this->nextMatchesForPPTournamentType($ppttId, $howMany);
        if(!$matches) return [];


        $ppTournamentType = $this->ppTournamentTypeRepository->getOne($ppttId);
        $level = $ppTournamentType['level'] ?? 1;
        $diversity = $this->checkDiversity($ppttId, $matches, $level);
        if(!$diversity) return [];

        $daysDiff=8;
        $filtered=[];
        while((!$diversity && count($filtered) < $howMany) || ($daysDiff < 55 && count($filtered) < $howMany)){
            $filtered = $this->filterDateAndRound($matches, $daysDiff);
            $daysDiff += 5;
            $diversity = $this->checkDiversity($ppttId, $filtered, $level);
        }

        if(!$diversity) return [];

        $picked = array();

        $groupedByLevel = $this->groupByLevelParent($filtered);
        shuffle($groupedByLevel[$level]);
        shuffle($groupedByLevel[$level][0]);
        array_push($picked, array_pop($groupedByLevel[$level][0]));
        if(!$groupedByLevel[$level][0]) unset($groupedByLevel[$level][0]);

        for($i=1; $i<$howMany; $i++){
            shuffle($groupedByLevel);
            shuffle($groupedByLevel[0]);
            $level = $groupedByLevel[0];
            shuffle($level[0]);
            array_push($picked, array_pop($level[0]));    
            $remaining = $howMany - count($picked);

            if(count($groupedByLevel) > $remaining){
                unset($groupedByLevel[0][0]);
                if(!$groupedByLevel[0]) unset($groupedByLevel[0]);
            }else{
                $groupedByLevel[0] = $level;
            }
        }
        
        return $picked;
    }

    private function checkDiversity(int $ppTournamentTypeId, array $matches, ?int $level=0){
        if($level && !in_array($level,array_column($matches,'league_level'))){
            return false;
        }
        return true;
    }

    private function groupByLevelParent(array $matches){
        $grouped = array();
        $levels = array_unique(array_column($matches, 'league_level'));
        foreach ($levels as $lvl) {
            $grouped[$lvl]= array_filter($matches, function ($m) use ($lvl){
                return $m['league_level'] == $lvl;
            });
        }
        foreach ($grouped as $lvl => $lvlMatches) {
            $grouped[$lvl] = $this->groupByParentLeague($lvlMatches);
        }
        return $grouped;
    }
    

    private function groupByParentLeague(array $matches){
        // Group matches by parent_id
        $groupedByParentLeague = [];
        foreach ($matches as $match) {
            $parentId = $match['league_parent_id'];
            if (!isset($groupedByParentLeague[$parentId])) {
                $groupedByParentLeague[$parentId] = [];
            }
            $groupedByParentLeague[$parentId][] = $match;
        }
        return $groupedByParentLeague;
    }  

    private function filterDateAndRound(array $matches, ?int $daysDiff =  8){
        $groupedByParentLeague=$this->groupByParentLeague($matches);
        // Filter matches to keep only the highest round for each group
        //to avoid round 2 group a and round 3 group b in euro cup
        $filteredMatches = [];
        foreach ($groupedByParentLeague as $parentId => $subLeague) {
            usort($subLeague, function ($a, $b) {
                return $b['round'] - $a['round']; // Sort by round in descending order
            });
            $highestRound = $subLeague[0]['round'];
            $filteredMatches = array_merge($filteredMatches, 
                array_filter($subLeague, function ($match) use ($highestRound) {
                    return $match['round'] == $highestRound;
                })
            );
        }
        
        $reasonableMatches = array_filter($filteredMatches, 
            function ($e) use ($daysDiff){
                return $e['date_start'] < date("Y-m-d H:i:s", strtotime('+ '.$daysDiff.' days'));
            }
        );

        return $reasonableMatches;
    }


    public function nextMatchesForPPTournamentType(int $ppTournamentTypeId,int $minAmount = 3){
        $leagues = $this->leagueFindService->getForPPTournamentType($ppTournamentTypeId, false);
        if(!$leagues)return;
        
        $leagueIds = array_column($leagues, 'id');
        
        $matches = array();
        foreach ($leagueIds as $id) {
            if($retrieved = $this->nextRoundForLeague($id)){
                $matches = array_merge($matches, $retrieved);
            }
        }
        if(!$matches || $matches < $minAmount){
            $matches = array();
            foreach ($leagueIds as $id) {
                if($retrieved = $this->nextMatchesForLeague($id)){
                    $matches = array_merge($matches, $retrieved);
                }
            }
        }

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