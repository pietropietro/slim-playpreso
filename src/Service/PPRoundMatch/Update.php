<?php

declare(strict_types=1);

namespace App\Service\PPRoundMatch;

use App\Service\BaseService;
use App\Repository\GuessRepository;
use App\Repository\PPRoundMatchRepository;


final class Update extends BaseService{
    public function __construct(
        protected PPRoundMatchRepository $ppRoundMatchRepository,
        protected GuessRepository $guessRepository,
    ){}

    public function swap(int $id, int $newMatchId){
        $this->guessRepository->changePPRMMatch($id, $newMatchId);
        $this->ppRoundMatchRepository->changeMatch($id, $newMatchId);
    }

}