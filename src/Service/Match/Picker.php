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
    
    public function pick(int $tournamentTypeId, int $howMany) : ?array{
        if(!$leagueIDs = $this->leagueService->getForPPTournamentType($tournamentTypeId, true)) return [];
        
        $matches = array();
        foreach ($leagueIDs as $id) {
            if($retrieved = $this->matchRepository->getNextRoundForLeague($id)){
                $matches = array_merge($matches, $retrieved);
            }
        }
        if(!$matches) return [];
        shuffle($matches);
        return array_slice($matches, 0, $howMany);
    }

}