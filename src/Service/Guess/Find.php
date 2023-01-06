<?php

declare(strict_types=1);

namespace App\Service\Guess;

use App\Service\BaseService;
use App\Service\Match;
use App\Repository\GuessRepository;


final class Find extends BaseService{
    public function __construct(
        protected GuessRepository $guessRepository,
        protected Match\Find $matchFindService,
    ){}

    public function notLocked(int $userId){
        $guesses = $this->guessRepository->getForUser($userId, true);
        foreach($guesses as &$guess){
            $guess['match'] = $this->matchFindService->getOne($guess['match_id']);
        }
        return $guesses;
    }

}