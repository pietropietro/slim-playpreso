<?php

declare(strict_types=1);

namespace App\Service\Stats;

use App\Service\BaseService;
use App\Service\UserParticipation;
use App\Repository\StatsRepository;

final class Find extends BaseService{
    public function __construct(
        protected StatsRepository $statsRepository,
        protected UserParticipation\Find $userParticipationFindService,
    ) {}
    
    public function bestUsers() {
        $aggregates = $this->statsRepository->bestUsers();
        foreach ($aggregates as &$value) {
            $value['user'] = array(
                "username" => $value['username'],
                "id" => $value['user_id'],
                "trophies" => $this->userParticipationFindService->getTrophies($value['user_id'])
            );
        }
        return $aggregates;
    }

}
