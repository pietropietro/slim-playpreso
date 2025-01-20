<?php

declare(strict_types=1);

namespace App\Service\Guess;

use App\Service\BaseService;
use App\Repository\GuessRepository;
use App\Repository\PPRoundMatchRepository;
use App\Repository\UserParticipationRepository;
use App\Repository\MatchRepository;
use App\Repository\UserRepository;

final class Create extends BaseService{
    public function __construct(
        protected GuessRepository $guessRepository,
        protected UserParticipationRepository $upRepository,
        protected PPRoundMatchRepository $ppRoundMatchRepository,
        protected MatchRepository $matchRepository,
        protected UserRepository $userRepository
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
        $matchId = $this->ppRoundMatchRepository->getMatchId($ppRoundMatchId);
        if(!$matchId)return false;

        if(!$this->matchRepository->isBeforeStartTime($matchId)){
            return false;
        }
        return $this->guessRepository->create($userId, $ppRoundMatchId);
    }

    /**
     * Build a "dummy guess" array to indicate the user hasn't locked or guessed yet.
     * used for MOTD and FLASH pptts so fat
     * 
     * @return array<string, mixed>
     */
    public function buildDummyGuess(int $userId, int $ppRoundMatchId): array
    {   
        $userPoints = $this->userRepository->getPoints($userId);
        $pprm = $this->ppRoundMatchRepository->getOne($ppRoundMatchId);

        // Lock cost might be NULL in DB. Treat that as zero or handle differently:
        $cost = $pprm['lock_cost'] ?? 0; // if it's null, default 0

        // Start with no error
        $canLock = null;

        // 1) Check if user has enough points
        if ($cost > $userPoints) {
            $canLock = 'toopoor';
        }

        // 2) Check match not started
        //    If match already started (or not found), set "toolate"
        if (!$this->matchRepository->isBeforeStartTime($pprm['match_id'])) {
            $canLock = 'toolate';
        }

        return [
            'id'          => 'dummy',
            'home'        => null,
            'away'        => null,
            'verified_at' => $canLock,
            'user_id' => $userId,
            'ppRoundMatch_id' => $ppRoundMatchId
        ];
    }

}