<?php

declare(strict_types=1);

namespace App\Service\PPRoundMatch;

use App\Service\BaseService;
use App\Repository\GuessRepository;
use App\Repository\PPRoundMatchRepository;


final class Delete extends BaseService{
    public function __construct(
        protected PPRoundMatchRepository $ppRoundMatchRepository,
        protected GuessRepository $guessRepository,
    ){}

    public function delete(int $id){
        $this->guessRepository->deleteForPPRMatch($id);
        $this->ppRoundMatchRepository->delete($id);
    }

}