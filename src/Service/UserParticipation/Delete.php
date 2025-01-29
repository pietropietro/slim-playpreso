<?php

declare(strict_types=1);

namespace App\Service\UserParticipation;
use App\Service\BaseService;
use App\Repository\UserParticipationRepository;
use App\Repository\UserRepository;

final class Delete  extends BaseService {

    public function __construct(
        protected UserParticipationRepository $userParticipationRepository,
        protected UserRepository $userRepository,
    ) {}

    public function removeInactive(int $tournamentId, string $tournamentColumn){
        $ups = $this->userParticipationRepository->getForTournament($tournamentColumn,$tournamentId);
        if(!$ups)return;

        foreach ($ups as $up) {
            if($this->userRepository->isInactive($up['user_id'])){
                $this->userParticipationRepository->delete($up['id']);
            }
        }
    }

}
