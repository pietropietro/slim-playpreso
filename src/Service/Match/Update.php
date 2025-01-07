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

    public function updateTeams(int $id, int $home_id, int $away_id, bool $is_external_id){
        if($is_external_id){
            $home_id = $this->teamRepository->idFromExternal($home_id);
            $away_id = $this->teamRepository->idFromExternal($away_id);
        }
        if($home_id && $away_id){
            $this->matchRepository->updateTeams($id, $home_id, $away_id);
        }
    }

    public function updateExternalId(int $id, int $newLs_id){
        return $this->matchRepository->updateExternalId($id, $newLs_id);
    }

    public function updateDateStart(int $id, string $date_start){
        $this->matchRepository->updateDateStart($id, $date_start);
        return $this->matchRepository->updateNotes($id, null);
    }

    public function updateNotes(int $id, ?string $notes = null){
        return $this->matchRepository->updateNotes($id, $notes);
    }

    public function updateLeague(int $id, int $league_id){
        return $this->matchRepository->updateLeague($id, $league_id);
    }
}

