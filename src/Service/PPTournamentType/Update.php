<?php

declare(strict_types=1);

namespace App\Service\PPTournamentType;

use App\Repository\PPTournamentTypeRepository;
use App\Service\BaseService;

final class Update extends BaseService
{
    public function __construct(
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
    ) {}

    public function update(int $id, array $data){
        return $this->ppTournamentTypeRepository->update($id, $data);
    }
}

