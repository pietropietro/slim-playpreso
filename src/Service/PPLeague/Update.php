<?php

declare(strict_types=1);

namespace App\Service\PPLeague;

use App\Repository\PPLeagueRepository;
use App\Service\BaseService;

final class Update  extends BaseService{
    public function __construct(
        protected PPLeagueRepository $ppLeagueRepository,
    ) {}

    public function setFinished(int $id){
        $this->ppLeagueRepository->setFinished($id);
    }

    public function setStarted(int $id){
        $this->ppLeagueRepository->setStarted($id);
    }

    //TODO HERE
}
