<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

final class Find  extends Base {

    public function getForTournament(string $tournamentColumn, int $tournamentId) :array{
        $ups = $this->userParticipationRepository->getForTournament($tournamentColumn, $tournamentId); 
        if($tournamentColumn === 'ppCupGroup_id'){
            //TODO refactor
            $ups = array_map(function ($up){
                $up['points_total'] = $this->userParticipationRepository
                    ->getCupPointsTotal($up['user_id'], $up['ppCup_id'], $up['joined_at']);
                return $up;
            }, $ups);
        }
        return $ups;
    }


    //TODO change playMode to ENUM
    public function getUserParticipations(int $userId, string $playMode, bool $active){
        $ups = $this->userParticipationRepository->getUserParticipations($userId, $playMode.'_id', $active, null);        
        foreach($ups as $upKey => $upItem){
            if($playMode === 'ppLeague'){
                $ups[$upKey] = $this->addPPLeagueData($ups[$upKey]);
            }
        }
        return $ups;
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
            $ppLeagueUps[$upKey] = $this->addPPLeagueData($ppLeagueUps[$upKey]);
        }

        //also add data to cup trophies

        $trophies['ppLeagues'] = $ppLeagueUps;
        return $trophies;
    }
}