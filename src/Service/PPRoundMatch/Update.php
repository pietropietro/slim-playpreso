<?php

declare(strict_types=1);

namespace App\Service\PPRoundMatch;

use App\Service\BaseService;
use App\Repository\PPRoundMatchRepository;


final class Update extends BaseService{
    public function __construct(
        protected PPRoundMatchRepository $ppRoundMatchRepository,
    ){}

    public function swap(int $id, int $newMatchId){
        $this->ppRoundMatchRepository->changeMatch($id, $newMatchId);
    }

}