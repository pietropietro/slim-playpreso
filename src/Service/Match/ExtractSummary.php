<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\League;
use App\Service\Team;
use App\Repository\MatchRepository;

final class ExtractSummary extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
    ) {}

    public function adminGetForMonth(int $year, int $month): array {
        $monthSummary = $this->matchRepository->getCountByMonth($year, $month);
    
        foreach ($monthSummary as &$daySummary) {
            $dayLeagues = json_decode($daySummary['matches_from'], true);
    
            // Count matches for each league
            $leagueMatchCount = $this->countMatches($dayLeagues);
    
            // Build the hierarchical structure and assign match counts
            $monthCountryMap = $this->buildHierarchy($dayLeagues, $leagueMatchCount);
    
            // Adjust parent league match counts
            $this->adjustParentLeagueCounts($monthCountryMap, $leagueMatchCount);
    
            // Convert associative array to indexed array
            $this->convertAssociativeToIndexed($monthCountryMap);
    
            $daySummary['matches_from'] = $monthCountryMap;
        }
    
        return $monthSummary;
    }
    
    private function countMatches(array $dayLeagues): array {
        $leagueMatchCount = [];
    
        foreach ($dayLeagues as $dayLeague) {
            $leagueId = $dayLeague['league_id'];
            if (!isset($leagueMatchCount[$leagueId])) {
                $leagueMatchCount[$leagueId] = 0;
            }
            $leagueMatchCount[$leagueId]++;
        }
    
        return $leagueMatchCount;
    }
    
    private function buildHierarchy(array $dayLeagues, array $leagueMatchCount): array {
        $monthCountryMap = [];
    
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
                // Top-level league or parent_id is the same as league_id
                $this->initializeParentLeague($monthCountryMap[$country], $leagueId, $league, $level);
                $monthCountryMap[$country][$leagueId]['league_day_count'] = $leagueMatchCount[$leagueId];
            } else {
                // Parent league initialization
                $this->initializeParentLeague($monthCountryMap[$country], $parentId, $parentName, $level);
    
                // Add sub-league if not present
                if (!$this->subLeagueExists($monthCountryMap[$country][$parentId]['subLeagues'], $leagueId)) {
                    $this->addSubLeague($monthCountryMap[$country][$parentId]['subLeagues'], $league, $leagueId, $leagueMatchCount[$leagueId]);
                }
    
                // Update the parent league's match count
                $monthCountryMap[$country][$parentId]['league_day_count'] += $leagueMatchCount[$leagueId];
            }
        }
    
        return $monthCountryMap;
    }
    
    private function initializeParentLeague(array &$countryLeagues, int $parentId, string $parentName, int $level): void {
        if (!isset($countryLeagues[$parentId])) {
            $countryLeagues[$parentId] = [
                'name' => $parentName,
                'id' => $parentId,
                'level' => $level,
                'subLeagues' => [],
                'league_day_count' => 0
            ];
        }
    }
    
    private function subLeagueExists(array $subLeagues, int $leagueId): bool {
        foreach ($subLeagues as $subLeague) {
            if ($subLeague['id'] === $leagueId) {
                return true;
            }
        }
        return false;
    }
    
    private function addSubLeague(array &$subLeagues, string $leagueName, int $leagueId, int $leagueDayCount): void {
        $subLeagues[] = [
            'name' => $leagueName,
            'id' => $leagueId,
            'league_day_count' => $leagueDayCount
        ];
    }
    
    private function adjustParentLeagueCounts(array &$monthCountryMap, array $leagueMatchCount): void {
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
    }
    
    private function convertAssociativeToIndexed(array &$monthCountryMap): void {
        foreach ($monthCountryMap as &$leaguesInCountry) {
            $leaguesInCountry = array_values($leaguesInCountry);
        }
    }

}