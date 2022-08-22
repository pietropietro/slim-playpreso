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
        usort($ups, fn($a, $b) => $a['score'] < $b['score'] ? 1 : 0);
        
        foreach($ups as $index => $upItem){
            $this->userParticipationRepository->updateScore($upItem['id'], $upItem['score']);
            $this->userParticipationRepository->updatePosition($upItem['id'], $index + 1);
        }
        return true;
    }

    public function setFinished(string $tournamentColumn, int $tournamentId){
        $this->userParticipationRepository->setFinished($tournamentColumn, $tournamentId);
    }
}