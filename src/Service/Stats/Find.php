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
    
    public function bestUsers(?int $userId = null) {
        $aggregates = $this->statsRepository->bestUsers();

        foreach ($aggregates as &$value) {
            $this->enrich($value);
        }

        $returnArray = array(
            "best" => $aggregates,
        );

        if($userId && !in_array($userId, array_column($aggregates,'user_id'))){
            $user_extra_stats = $this->statsRepository->bestUsers($userId)[0];
            $this->enrich($user_extra_stats);
            $returnArray['currentUserStat'] = $user_extra_stats;
        }

        return $returnArray;
    }

    private function enrich(&$userStat){
        $userStat['user'] = array(
            "username" => $userStat['username'],
            "id" => $userStat['user_id'],
            "trophies" => $this->userParticipationFindService->getTrophies($userStat['user_id'])
        );
    }

}
