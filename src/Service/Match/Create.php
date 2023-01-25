<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Repository\MatchRepository;
use App\Repository\TeamRepository;

final class Create extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected TeamRepository $teamRepository,
    ) {}

    public function create(
        int $ls_id, 
        int $leagueId, 
        int $homeId, 
        int $awayId, 
        int $round, 
        string $dateStart
    ) : bool {
        return $this->matchRepository->create($ls_id, $leagueId, $homeId, $awayId, $round, $dateStart);
    }
}
