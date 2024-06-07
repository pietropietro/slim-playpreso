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


    public function addNameToStandings(?array &$standings) {
        if(!$standings) return;
        $teamIds = array_column($standings, 'id'); // Extract team IDs
        $teams = $this->teamRepository->get($teamIds, ['id', 'name']); // Fetch team names

        // Create an associative array with team ID as the key and team name as the value
        $teamNames = [];
        foreach ($teams as $team) {
            $teamNames[$team['id']] = $team['name'];
        }

        // Merge team names into standings
        foreach ($standings as &$standing) {
            $standing->name = $teamNames[$standing->id] ?? 'Unknown';
        }

        return $standings;
    }

}
