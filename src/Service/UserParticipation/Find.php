<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

final class Find  extends Base {

    public function getForTournament(string $tournamentColumn, int $tournamentId) :array{
        $ups = $this->userParticipationRepository->getForTournament($tournamentColumn, $tournamentId); 
        return $ups;
    }

    public function countInTournament(string $tournamentColumn, int $tournamentId){
        return $this->userParticipationRepository->count($tournamentColumn, $tournamentId);
    }

    public function getUserParticipationsEnriched(int $userId, ?string $playMode, bool $active = true){
        $ups = $this->userParticipationRepository->getUserParticipations(
            $userId, 
            $playMode ? $playMode.'_id' : null,
            $active, 
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
        $ppLeagueUps = $this->userParticipationRepository->getUserParticipations(
            $userId, 'ppLeague_id', false, (int)$_SERVER['PPLEAGUE_TROPHY_POSITION']
        );  

        $ppCupUps = $this->userParticipationRepository->getUserParticipations(
            $userId, 'ppCup_id', false, (int)$_SERVER['PPLEAGUE_TROPHY_POSITION']
        );  
        
        if(!$ppLeagueUps && !$ppCupUps) return null;

        foreach($ppLeagueUps as $upKey => $upItem){
            $ppLeagueUps[$upKey] = $this->enrich($ppLeagueUps[$upKey]);
        }

        //also add data to cup trophies

        $trophies['ppLeagues'] = $ppLeagueUps;
        return $trophies;
    }
}
