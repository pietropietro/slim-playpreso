<?php

declare(strict_types=1);

namespace App\Service\Stats;

use App\Service\BaseService;
use App\Repository\StatsRepository;

final class Find extends BaseService{
    public function __construct(
        protected StatsRepository $statsRepository,
    ) {}
    
    public function bestUsers() {
        return $this->statsRepository->bestUsers();
    }

}
