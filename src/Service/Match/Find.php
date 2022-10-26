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
    
    public function getOne(int $id) : array {
        $match = $this->matchRepository->getOne($id);
        return $this->enrich($match);
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
        $match['homeTeam'] = $this->teamRepository->getOne($match['home_id']);
        $match['awayTeam'] = $this->teamRepository->getOne($match['away_id']);
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

