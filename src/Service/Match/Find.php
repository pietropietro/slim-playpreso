<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\League;
use App\Service\Team;
use App\Repository\MatchRepository;

final class Find extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected League\Find $leagueFindService,
        protected Team\Find $teamFindService,
    ) {}

    private function enrich(array $match, ?bool $withStats=true){
        $match['homeTeam'] = $match['home_id'] ? $this->teamFindService->getOne($match['home_id'], false, $withStats) : null;
        $match['awayTeam'] = $match['away_id'] ? $this->teamFindService->getOne($match['away_id'], false, $withStats) : null;
        $match['league'] = $this->leagueFindService->getOne($match['league_id'], false,$withStats);
        return $match;
    }

    public function get(array $ids){
        $matches = $this->matchRepository->get($ids);
        return $this->enrichAll($matches);
    }
    
    //withStats: league standings + teams last matches WDL
    public function getOne(
        int $id, 
        ?bool $is_external_id=false, 
        ?bool $withTeams=true, 
        ?bool $withStats=true
    ) : ?array {
        $match = $this->matchRepository->getOne($id, $is_external_id);
        return $withTeams ? $this->enrich($match, $withStats) : $match;
    }

    public function getOneByLeagueRoundAndTeams(int $leagueId, int $round, int $homeId, int $awayId){
        return $this->matchRepository->getOneByLeagueRoundAndTeams($leagueId, $round, $homeId, $awayId);
    }

    public function adminGet(array $ids) : ?array {
        if(!$ids)return [];
        $matches = $this->matchRepository->adminGet(ids: $ids);
        return $this->enrichAll($matches);
    }


    public function adminGetForWeek(int $days_diff) : array {
        $adminMatches = array();
        for($i=$days_diff-3; $i<$days_diff+4; $i++){
            $dateString = date("Y-m-d", strtotime(sprintf("%+d",$i).' days'));
            $retrieved = $this->matchRepository->adminGet(
                date: $dateString
            );
            $adminMatches[$dateString] = $this->enrichAll($retrieved);
        }
        return $adminMatches;
    }


    public function adminGetSummaryForMonth(int $year, int $month): array {
        $monthSummary = $this->matchRepository->getCountByMonth($year, $month);
    
        foreach ($monthSummary as &$daySummary) {
            $dayLeagues = json_decode($daySummary['matches_from'], true);
            $monthCountryMap = [];
            $leagueMatchCount = [];
    
            // First, count all matches for each league and sub-league
            foreach ($dayLeagues as $dayLeague) {
                $leagueId = $dayLeague['league_id'];
                if (!isset($leagueMatchCount[$leagueId])) {
                    $leagueMatchCount[$leagueId] = 0;
                }
                $leagueMatchCount[$leagueId]++;
            }
    
            // Now, build the hierarchy and assign match counts
            foreach ($dayLeagues as $dayLeague) {
                $country = $dayLeague['country'];
                $leagueId = $dayLeague['league_id'];
                $parentId = $dayLeague['parent_id'];
                $league = $dayLeague['league'];
                $parentName = $dayLeague['parent_name'];
                $level = $dayLeague['level'];
    
                if (!isset($monthCountryMap[$country])) {
                    $monthCountryMap[$country] = [];
                }
    
                if ($parentId === null || $parentId === $leagueId) {
                    // It's a top-level league or the parent_id is the same as league_id
                    if (!isset($monthCountryMap[$country][$leagueId])) {
                        $monthCountryMap[$country][$leagueId] = [
                            'name' => $league,
                            'id' => $leagueId,
                            'level' => $level,
                            'subLeagues' => [],
                            'league_day_count' => 0
                        ];
                    }
                    $monthCountryMap[$country][$leagueId]['league_day_count'] = $leagueMatchCount[$leagueId];
                } else {
                    // Initialize parent league if not created yet
                    if (!isset($monthCountryMap[$country][$parentId])) {
                        $monthCountryMap[$country][$parentId] = [
                            'name' => $parentName,
                            'id' => $parentId,
                            'level' => $level,
                            'subLeagues' => [],
                            'league_day_count' => 0
                        ];
                    }
    
                    // Add sub-league if not there
                    $subLeagueExists = false;
                    foreach ($monthCountryMap[$country][$parentId]['subLeagues'] as &$subLeague) {
                        if ($subLeague['id'] === $leagueId) {
                            $subLeagueExists = true;
                            break;
                        }
                    }
                    if (!$subLeagueExists) {
                        $monthCountryMap[$country][$parentId]['subLeagues'][] = [
                            'name' => $league,
                            'id' => $leagueId,
                            'league_day_count' => $leagueMatchCount[$leagueId]
                        ];
                    }
    
                    // Update the parent league's match count by summing sub-leagues correctly
                    $monthCountryMap[$country][$parentId]['league_day_count'] += $leagueMatchCount[$leagueId];
                }
            }
    
            // Correct the parent league's match count by including direct matches
            foreach ($monthCountryMap as $country => &$leagues) {
                foreach ($leagues as $parentId => &$parentLeague) {
                    if (!empty($parentLeague['subLeagues'])) {
                        $parentLeagueCount = 0;
                        foreach ($parentLeague['subLeagues'] as $subLeague) {
                            $parentLeagueCount += $subLeague['league_day_count'];
                        }
                        // Add direct matches of the parent league itself
                        $parentLeagueCount += $leagueMatchCount[$parentId] ?? 0;
                        $parentLeague['league_day_count'] = $parentLeagueCount;
                    }
                }
            }
    
            // Convert associative array to indexed array
            foreach ($monthCountryMap as &$leaguesInCountry) {
                $leaguesInCountry = array_values($leaguesInCountry);
            }
    
            $daySummary['matches_from'] = $monthCountryMap;
        }
    
        return $monthSummary;
    }
    
    
    
    
    
    
    
    
    
    


    public function adminGetForLeague(int $leagueId, bool $next = true){
        $matches=$this->matchRepository->getMatchesForLeagues(
            array($leagueId), 
            $next ? 0 : null, 
            $next ? null : 0, 
            $next ? 'ASC' : 'DESC', 
            10
        );
        
        return $this->enrichAll($matches);
    }

    public function hasLiveMatch(array $ids){
        return $this->matchRepository->hasLiveMatch(ids: $ids);
    }

    public function getNextMatchInPPRound(int $ppRound_id){
        $match = $this->matchRepository->getNextInPPRound($ppRound_id);
        if(!$match)return null;
        return $this->enrich($match);
    }

    public function getLastMatchInPPTournament(string $type, int $typeId){
        $match = $this->matchRepository->getLastInPPTournament($type, $typeId);
        if(!$match)return null;
        return $this->enrich($match, false);
    }

    public function getNextMatchInPPTournament(string $type, int $typeId){
        $match = $this->matchRepository->getNextInPPTournament($type, $typeId);
        if(!$match)return null;
        return $this->enrich($match, false);
    }

    private function enrichAll($matches){
        foreach ($matches as &$match) {
            $match = $this->enrich($match);
        }
        return $matches;
    }

    public function isBeforeStartTime(int $id){
        return $this->matchRepository->isBeforeStartTime($id);
    }
    
}

