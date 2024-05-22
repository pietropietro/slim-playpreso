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


    public function adminGetSummaryForMonth(int $month_diff) : array {
        $matchSummary = $this->matchRepository->getCountByMonth($month_diff);
    
        // Decode JSON data and build hierarchy for each match
        foreach ($matchSummary as &$match) {
            $entries = json_decode($match['match_from'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $matchFromMap = [];
                foreach ($entries as $entry) {
                    $country = $entry['country'];
                    $league = $entry['league'];
                    $parentId = $entry['parent_id'];
                    $leagueId = $entry['league_id'];
                    $parentName = $entry['parent_name'];
                    $level = $entry['level'];
    
                    if (!isset($matchFromMap[$country])) {
                        $matchFromMap[$country] = [];
                    }
    
                    if ($parentId === null || $parentId === $leagueId) {
                        // It's a top-level league or the parent_id is the same as league_id
                        if (!isset($matchFromMap[$country][$leagueId])) {
                            $matchFromMap[$country][$leagueId] = [
                                'name' => $league,
                                'id' => $leagueId,
                                'level' => $level,
                                'subLeagues' => []
                            ];
                        }
                    } else {
                        // It's a child league
                        if (!isset($matchFromMap[$country][$parentId])) {
                            $matchFromMap[$country][$parentId] = [
                                'name' => $parentName,
                                'id' => $parentId,
                                'subLeagues' => []
                            ];
                        }
                        // Check if subLeague already exists
                        $subLeagueExists = false;
                        foreach ($matchFromMap[$country][$parentId]['subLeagues'] as $subLeague) {
                            if ($subLeague['id'] === $leagueId) {
                                $subLeagueExists = true;
                                break;
                            }
                        }
                        if (!$subLeagueExists) {
                            $matchFromMap[$country][$parentId]['subLeagues'][] = [
                                'name' => $league,
                                'id' => $leagueId,
                            ];
                        }
                    }
                }
    
                // Convert associative array to indexed array
                foreach ($matchFromMap as &$leagues) {
                    $leagues = array_values($leagues);
                    foreach ($leagues as &$league) {
                        if (isset($league['subLeagues']) && empty($league['subLeagues'])) {
                            unset($league['subLeagues']);
                        }
                    }
                }
    
                $match['match_from'] = $matchFromMap;
            } else {
                // Handle JSON decode error
                $match['match_from'] = [];
            }
        }
    
        return $matchSummary;
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

