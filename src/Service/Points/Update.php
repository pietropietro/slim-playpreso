<?php

declare(strict_types=1);

namespace App\Service\Points;

use App\Repository\UserRepository;
use App\Repository\GuessRepository;
use App\Service\BaseService;

final class Update extends BaseService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected GuessRepository $guessRepository,
    ) {}

    public function minus(int $userId, int $points){
        if($this->userRepository->getPoints($userId) < $points){
            throw new \App\Exception\NotFound("can't afford", 400);
        }
        return $this->userRepository->minus($userId, $points);
    }

    public function plus(int $userId, ?int $points){
        if(!$points)return;
        return $this->userRepository->plus($userId, $points);
    }


    public function payOutJackpot(array $guesses, int $jackpot){
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
                $this->plus($winGuess['user_id'], $split);
                $this->guessRepository->markWinner($winGuess['id'], $split);
            }
        }
    }
}

