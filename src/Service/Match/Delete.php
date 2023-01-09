<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Repository\MatchRepository;
use App\Repository\TeamRepository;
use App\Service\BaseService;

final class Delete extends BaseService
{
    public function __construct(
        protected MatchRepository $matchRepository,
    ) {}

    public function delete(int $id){
        return $this->matchRepository->delete($id);
    }
}

