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

    public function create(Object $eventObj, int $league_id) : bool{
        $home_id = $this->teamRepository->idFromExternal((int)$eventObj->T1[0]->ID);
        $away_id = $this->teamRepository->idFromExternal((int)$eventObj->T2[0]->ID);
        $round = (int)$eventObj->Ern;
        return $this->matchRepository->create((int)$eventObj->Eid, $league_id, $home_id, $away_id, $round, (string)$eventObj->Esd);
    }
}
