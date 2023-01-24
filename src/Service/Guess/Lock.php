<?php

declare(strict_types=1);

namespace App\Service\Guess;

use App\Service\BaseService;
use App\Repository\GuessRepository;
use App\Repository\MatchRepository;


final class Lock extends BaseService{
    public function __construct(
        protected GuessRepository $guessRepository,
        protected MatchRepository $matchRepository,
    ){}

    public function lock(int $id, int $userId, int $home, int $away){
        $guess = $this->guessRepository->getOne($id);
        if($guess['user_id'] != $userId){
            throw new \App\Exception\NotFound("forbidden", 403);
        }
        if($guess['guessed_at'] || $guess['verified_at']){
            throw new \App\Exception\NotFound("forbidden", 403);
        }
        if(!$this->matchRepository->isBeforeStartTime($guess['match_id'])){
            throw new \App\Exception\NotFound("match started.", 403);
        }

        $this->guessRepository->lock($id, $home, $away);
    }

}