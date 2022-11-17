<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Repository\MatchRepository;
use App\Repository\TeamRepository;
use App\Service\BaseService;

final class Update extends BaseService
{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected TeamRepository $teamRepository,
    ) {}

    public function updateTeams($id, $home_id, $away_id, $is_external_id){
        if($is_external_id){
            $home_id = $this->teamRepository->idFromExternal($home_id);
            $away_id = $this->teamRepository->idFromExternal($away_id);
        }
        if($home_id && $away_id){
            $this->matchRepository->updateTeams($id, $home_id, $away_id);
        }
    }

    public function updateDateStart(int $id, string $date_start){
        return $this->matchRepository->updateDateStart($id, $date_start);
    }

    public function updateNotes(int $id, string $notes){
        return $this->matchRepository->updateNotes($id, $notes);
    }
}

