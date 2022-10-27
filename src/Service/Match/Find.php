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


    public function get(int $days_diff) : array {
        $adminMatches = array();
        for($i=$days_diff-3; $i<$days_diff+4; $i++){
            $dateString = date("Y-m-d", strtotime(sprintf("%+d",$i).' days'));
            $retrieved = $this->matchRepository->get(
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

    private function enrich($match){
        $match['homeTeam'] = $match['home_id'] ? $this->teamRepository->getOne($match['home_id']) : null;
        $match['awayTeam'] = $match['away_id'] ? $this->teamRepository->getOne($match['away_id']) : null;
        $match['league'] = $this->leagueService->getOne($match['league_id']);
        return $match;
    }

    private function enrichAll($matches){
        foreach ($matches as &$match) {
            $match = $this->enrich($match);
        }
        return $matches;
    }
    
}

