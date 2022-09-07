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
        $leagueIDs = $this->leagueService->getForPPTournamentType($tournamentTypeId, true);
        $matches = $this->matchRepository->getNextMatchesForLeagues($leagueIDs);
        if(count($matches)<3)return null;
        shuffle($matches);
        return array_slice($matches, 0, 3);
    }

}