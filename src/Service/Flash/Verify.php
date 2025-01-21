<?php

declare(strict_types=1);

namespace App\Service\Flash;

use App\Service\BaseService;
use App\Service\Points;
use App\Repository\FlashRepository;
use App\Repository\GuessRepository;

final class Verify extends BaseService{
    public function __construct(
        protected FlashRepository $flashRepository,
        protected GuessRepository $guessRepository,
        protected Points\Update $pointsUpdateService,
    ) {}
    
    public function checkIfVerify(int $matchId){
        $pprmFlash = $this->flashRepository->getWithMatch($matchId);
        if(!$pprmFlash)return;

        $guesses = $this->guessRepository->getForPPRoundMatch($pprmFlash['id']);
        if (empty($guesses)) {
            // No one guessed => no winners
            return;
        }

        $jackpot = $pprmFlash['lock_cost'] * count($guesses);
        $maxPoints = $guesses[0]['points'] ?? 0;

        // Collect all guesses that match $maxPoints
        $winners = [];
        foreach ($guesses as $g) {
            if ($g['points'] === $maxPoints) {
                $winners[] = $g;
            } else {
                // Since guesses are sorted desc, once we hit a guess with fewer points,
                // no need to keep checking
                break;
            }
        }

        // If we have at least one winner, split the jackpot
        $winnerCount = count($winners);
        if ($winnerCount > 0) {
            $split = (int) floor($jackpot / $winnerCount);

            foreach ($winners as $winGuess) {
                $this->pointsUpdateService->plus($winGuess['user_id'], $split);
                $this->guessRepository->markWinner($winGuess['id'], $split);
            }
        }
      
    }

}