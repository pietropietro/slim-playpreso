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

    public function get() : array {
        $matches = $this->matchRepository->get();
        foreach ($matches as $key => $match) {
            $matches[$key] = $this->enrich($match);
        }
        return $matches;
    }

    private function enrich($match){
        $match['homeTeam'] = $this->teamRepository->getOne($match['home_id']);
        $match['awayTeam'] = $this->teamRepository->getOne($match['away_id']);
        $match['league'] = $this->leagueService->getOne($match['league_id']);
        return $match;
    }
    
}

