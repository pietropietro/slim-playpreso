<?php

declare(strict_types=1);

namespace App\Service\Guess;

use App\Service\BaseService;
use App\Repository\GuessRepository;
use App\Repository\UserParticipationRepository;

final class Create extends BaseService{
    public function __construct(
        protected GuessRepository $guessRepository,
        protected UserParticipationRepository $upRepository,
    ) {}
    
    public function createForParticipants(int $ppRoundMatchId, int $matchId, string $tournamentColumn, int $tournamentId){
        $ups = $this->upRepository->getForTournament($tournamentColumn, $tournamentId);
        foreach ($ups as $key => $up) {
            $this->guessRepository->create($up['user_id'], $matchId, $ppRoundMatchId);
        }
        return true;
    }

}