<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

final class Update  extends Base {

    public function update(string $type, int $typeId) : bool{
        $ups = $this->userParticipationRepository->getForTournament($type, $typeId);
        foreach ($ups as $upKey => $upItem) {
            $ups[$upKey]['score'] = $this->guessRepository->getUpScore($upItem['user_id'], $type, $typeId);
        }

        ////TODO also sort by number of PRESO!, less MISSED, 1X2, UO, GG
        usort($ups, fn($a, $b) => $b['score'] <=> $a['score']);
        
        foreach($ups as $index => $upItem){
            $this->userParticipationRepository->update($upItem['id'], $upItem['score'], $index + 1);
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