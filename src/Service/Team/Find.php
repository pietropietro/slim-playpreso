<?php

declare(strict_types=1);

namespace App\Service\Team;

use App\Service\BaseService;
use App\Repository\TeamRepository;

final class Find extends BaseService{
    public function __construct(
        protected TeamRepository $teamRepository,
    ) {}

    private function enrich($team){
        if(!$team) return null;
        $team['lastMatches'] = $this->teamRepository->getLastResults($team['id']);
        return $team;
    }

    public function idFromExternal(int $ls_id) : ?int{
        return $this->teamRepository->idFromExternal($ls_id);
    }

    // public function getInternalExternalIdPair() : ?array {
    //     $pairs = $this->teamRepository->getInternalExternalIdPair();
    //     return $pairs;
    // }

    
    public function getOne(int $id, ?bool $is_external_id=false, ?bool $enrich=false ) : ?array {
        $team = $this->teamRepository->getOne($id, $is_external_id);
        return $enrich ? $this->enrich($team) : $team;
    }

}
