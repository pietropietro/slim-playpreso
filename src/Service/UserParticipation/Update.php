<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

final class Update  extends Base {

    public function update(string $tournamentColumn, int $tournamentId) : bool{
        $ups = $this->userParticipationRepository->getForTournament($tournamentColumn, $tournamentId);
        foreach ($ups as &$upItem) {
            $user_participation_result = $this->guessRepository->countUpNumbers($upItem['user_id'], $tournamentColumn, $tournamentId);
            $upItem['tot_points'] = (int)$user_participation_result['tot_points'] ?? null;
            $upItem['tot_locked'] = (int)$user_participation_result['tot_locked'] ?? 0;
            $upItem['tot_preso'] = (int)$user_participation_result['tot_preso'] ?? 0;
            $upItem['tot_unox2'] = (int)$user_participation_result['tot_unox2'] ?? 0;
            
            if($tournamentColumn === 'ppCupGroup_id'){
                $previousGroupsPoints = $this->userParticipationRepository->getOverallPPCupPoints($upItem['user_id'], $upItem['ppCup_id'], joinedBefore: $upItem['joined_at']) ?? null;
                if(!$previousGroupsPoints)continue;
                $upItem['tot_cup_points'] =  $previousGroupsPoints + $upItem['tot_points'];
            }
        }
       
        usort($ups, fn($a, $b) =>
            [$b['tot_points'], $b['tot_cup_points'], $b['tot_locked'], $b['tot_preso'], $b['tot_unox2']] 
                <=> 
            [$a['tot_points'], $a['tot_cup_points'], $a['tot_locked'], $a['tot_preso'], $a['tot_unox2']]
        );


        foreach($ups as $index => $up){
            $this->userParticipationRepository->update(
                id: $up['id'], 
                tot_points: (int)$up['tot_points'],
                tot_unox2:  (int)$up['tot_unox2'],
                tot_locked: (int)$up['tot_locked'],
                tot_preso:  (int)$up['tot_preso'],
                tot_cup_points:  (int)$up['tot_cup_points'] ?? null,
                position: $index + 1
            );
        }
        return true;
    }

    public function setFinished(string $tournamentColumn, int $tournamentId){
        $this->userParticipationRepository->setFinished($tournamentColumn, $tournamentId);
    }

    public function setStarted(string $tournamentColumn, int $tournamentId){
        $this->userParticipationRepository->setStarted($tournamentColumn, $tournamentId);
    }
}
