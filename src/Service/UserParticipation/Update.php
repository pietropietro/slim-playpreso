<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;

final class Update  extends Base {

    public function update(string $type, int $typeId){
        $ups = $this->userParticipationRepository->getTournamentParticipations($type, $typeId);

        foreach ($ups as $upKey => $upItem) {
            //TODO put in ppRound service
            $ups[$upKey]['score'] = $this->guessRepository->getUpScore($upItem['user_id'], $type, $typeId);
        }

        ////TODO also sort by number of PRESO!, less MISSED, 1X2, UO, GG
        usort($ups, fn($a, $b) => $a['score'] < $b['score'] ? 1 : 0);
        
        foreach($ups as $index => $upItem){
            //TODO add updated_at
            $this->userParticipationRepository->updateScore($upItem['id'], $upItem['score']);
            $this->userParticipationRepository->updatePosition($upItem['id'], $index + 1);
        }

        return true;
    }
}