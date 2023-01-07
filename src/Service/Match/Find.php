<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\League;
use App\Repository\TeamRepository;
use App\Repository\MatchRepository;

final class Find extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected TeamRepository $teamRepository,
        protected League\Find $leagueService,
    ) {}
    
    public function getOne(int $id, ?bool $is_external_id=false, ?bool $enrich=true) : ?array {
        $match = $this->matchRepository->getOne($id, $is_external_id);
        return $enrich ? $this->enrich($match) : $match;
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

    public function adminGetForLeague($league_id){
        $lastMatch=$this->matchRepository->getMatchesForLeagues(array($league_id), null, 0, 'DESC', 1);
        $nextMatch=$this->matchRepository->getMatchesForLeagues(array($league_id), 0, null, 'ASC', 1);
        
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

    private function enrich(array $match, ?bool $withStandings=true){
        $match['homeTeam'] = $match['home_id'] ? $this->teamRepository->getOne($match['home_id']) : null;
        $match['awayTeam'] = $match['away_id'] ? $this->teamRepository->getOne($match['away_id']) : null;
        $match['league'] = $this->leagueService->getOne($match['league_id'], $withStandings);
        return $match;
    }
    

    private function enrichAll($matches){
        foreach ($matches as &$match) {
            $match = $this->enrich($match);
        }
        return $matches;
    }
    
}

