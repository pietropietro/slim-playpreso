<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

final class Update  extends Base {

    public function update(string $tournamentColumn, int $tournamentId) : bool{
        $ups = $this->userParticipationRepository->getForTournament($tournamentColumn, $tournamentId);
        foreach ($ups as $upKey => $upItem) {
            $user_participation_result = $this->guessRepository->countUpNumbers($upItem['user_id'], $tournamentColumn, $tournamentId);
            $ups[$upKey]['tot_points'] = $user_participation_result['tot_points'];
            $ups[$upKey]['tot_locked'] = $user_participation_result['tot_locked'] ?? 0;
            $ups[$upKey]['tot_preso'] = $user_participation_result['tot_preso'] ?? 0;
            $ups[$upKey]['tot_unox2'] = $user_participation_result['tot_unox2'] ?? 0;
        }
       

        usort($ups, fn($a, $b) =>
            [$b['tot_points'], $b['tot_locked'], $b['tot_preso'], $b['tot_unox2']] 
                <=> 
            [$a['tot_points'], $a['tot_locked'], $a['tot_preso'], $a['tot_unox2']]
        );

        foreach($ups as $index => $upItem){
            $this->userParticipationRepository->update(
                id: $upItem['id'], 
                tot_points: (int)$upItem['tot_points'],
                tot_unox2:  (int)$upItem['tot_unox2'],
                tot_locked: (int)$upItem['tot_locked'],
                tot_preso:  (int)$upItem['tot_preso'],
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