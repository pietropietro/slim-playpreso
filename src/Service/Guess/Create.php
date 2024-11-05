<?php

declare(strict_types=1);

namespace App\Service\Guess;

use App\Service\BaseService;
use App\Repository\GuessRepository;
use App\Repository\MatchRepository;
use App\Repository\UserParticipationRepository;

final class Create extends BaseService{
    public function __construct(
        protected GuessRepository $guessRepository,
        protected UserParticipationRepository $upRepository,
        protected MatchRepository $matchRepository,
    ) {}
    
    public function createForParticipants(int $ppRoundMatchId, string $tournamentColumn, int $tournamentId){
        $ups = $this->upRepository->getForTournament($tournamentColumn, $tournamentId);
        foreach ($ups as $key => $up) {
            if($_SERVER['DEBUG']){
                $this->guessRepository->createdebug($up['user_id'], $ppRoundMatchId);
                continue;
            }
            $this->create($up['user_id'], $ppRoundMatchId);
        }
        return true;
    }

    public function create(int $userId, int $ppRoundMatchId){
        if(!$this->matchRepository->isBeforeStartTime($matchId)){
            return false;
        }
        return $this->guessRepository->create($userId, $ppRoundMatchId);
    }

}