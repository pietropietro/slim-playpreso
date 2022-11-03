<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\League;
use App\Repository\MatchRepository;

final class Picker extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected League\Find $leagueService,
    ) {}
    
    public function pick(int $tournamentTypeId) : ?array{
        if(!$leagueIDs = $this->leagueService->getForPPTournamentType($tournamentTypeId, true)) return [];

        $plus_days = 8;
        $matches = $this->matchRepository->getMatchesForLeagues($leagueIDs, from_days_diff: 1, until_days_diff: $plus_days);
        while(count($matches)<3 && $plus_days < 30){
            $plus_days += 4;
            $matches = $this->matchRepository->getMatchesForLeagues($leagueIDs, from_days_diff: 1, until_days_diff: $plus_days);
        }

        shuffle($matches);
        return array_slice($matches, 0, 3);
    }

}