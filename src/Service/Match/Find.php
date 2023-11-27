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
        $match['league'] = $this->leagueFindService->getOne($match['league_id'], $withStats);
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

    public function adminGetAggregatedForWeek(int $days_diff) : array {
        $adminAggr = array();
        for($i=$days_diff-3; $i<$days_diff+4; $i++){
            $dateString = date("Y-m-d", strtotime(sprintf("%+d",$i).' days'));
            $retrieved = $this->matchRepository->adminGetAggregated(
                date: $dateString
            );
            $adminAggr[$dateString] = $retrieved;
        }
        return $adminAggr;
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

    public function adminGetForLeague(int $leagueId){
        $lastMatch=$this->matchRepository->getMatchesForLeagues(array($leagueId), null, 0, 'DESC', 1);
        $nextMatch=$this->matchRepository->getMatchesForLeagues(array($leagueId), 0, null, 'ASC', 1);
        
        return array(
            $lastMatch ? $this->enrich($lastMatch[0]) : null,
            $nextMatch ? $this->enrich($nextMatch[0]) : null
        );
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

