<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

final class Find  extends Base {

    public function getForTournament(string $tournamentColumn, int $tournamentId) :array{
        $ups = $this->userParticipationRepository->getForTournament($tournamentColumn, $tournamentId); 
        foreach ($ups as &$up) {
            $up['user']['id'] = $up['user_id'];
            $up['user']['username'] = $up['username'];
            $up['user']['trophies'] = $this->getTrophies($up['user']['id']);
        }
        return $ups;
    }

    public function countInTournament(string $tournamentColumn, int $tournamentId){
        return $this->userParticipationRepository->count($tournamentColumn, $tournamentId);
    }

    public function getForUser(
        int $userId, 
        ?string $playMode, 
        ?bool $started = null, 
        ?bool $finished = null, 
    ){
        $ups = $this->userParticipationRepository->getForUser(
            $userId, 
            $playMode ? $playMode.'_id' : null,
            $started, 
            $finished, 
            null
        );        
        foreach($ups as &$up){
            $this->enrich($up, $userId);
        }
        return $ups;
    }

    public function isUserInTournament(int $userId, string $tournamentColumn, int $tournamentId){
        return $this->userParticipationRepository->isUserInTournament($userId, $tournamentColumn, $tournamentId);
    }

    public function isUserInTournamentType(int $userId, int $ppTournamentType_id){
        return $this->userParticipationRepository->isUserInTournamentType($userId, $ppTournamentType_id);
    }

    
    public function getTrophies(int $userId){
        $ppLeagueUps = $this->userParticipationRepository->getForUser(
            $userId, 'ppLeague_id', started: null, finished: true, minPosition: 1
        );  

        //TODO
        $ppCupWins = $this->userParticipationRepository->getCupWins($userId);

        $trophiesUP = array_merge($ppLeagueUps, $ppCupWins);
        foreach ($trophiesUP as &$trophyUP) {
            $trophyUP['ppTournamentType'] = $this->ppTournamentTypeRepository->getOne($trophyUP['ppTournamentType_id']);
        }
        
        
        return $trophiesUP;
    }
}
