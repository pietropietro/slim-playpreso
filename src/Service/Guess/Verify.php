<?php

declare(strict_types=1);

namespace App\Service\Guess;

use App\Service\BaseService;
use App\Service\Score;
use App\Service\User;
use App\Repository\GuessRepository;


final class Verify extends BaseService{
    public function __construct(
        protected GuessRepository $guessRepository,
        protected Score\Calculate $scoreService,
        protected User\Points $pointsService
    ){}

    public function verify(int $matchId, int $scoreHome, int $scoreAway){
        $guesses = $this->guessRepository->getForMatch($matchId, true);

        foreach ($guesses as $key => $guess) {
            $result = $this->scoreService->calculate($scoreHome,$scoreAway,$guess['home'],$guess['away']);
            $this->guessRepository->verify($guess['id'], $result['unox2'], $result['uo25'], $result['ggng'], $result['preso'], $result['score']);
            $this->pointsService->plus($guess['user_id'], $result['score']);
        }
    }

}