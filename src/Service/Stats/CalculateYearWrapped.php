<?php

declare(strict_types=1);

namespace App\Service\Stats;

use App\Service\BaseService;
use App\Service\Trophy;
use App\Repository\StatsRepository;

final class CalculateYearWrapped extends BaseService{
    public function __construct(
        protected StatsRepository $statsRepository,
        protected Trophy\Find $trophyFindService,
    ) {}

    public function calculateWrapped(){
        $year = 2023;
        $userId = 278;

        $mainSummary = $this->statsRepository->getUserMainSummary($userId, $year);
        $commonLock = $this->statsRepository->getCommonLock(null, $userId, $year);
        $missedCount = $this->statsRepository->getUserMissedCount($userId,$year);
        
        $commonTeams = $this->statsRepository->getUserCommonTeams($userId, $year);
        $highestAvgTeams = $this->statsRepository->getUserHighestAverageTeams($userId, $year);
        
        $commonLeagues = $this->statsRepository->getUserLeagues($userId, $year);
        $bestLeagues = $this->statsRepository->getUserLeagues($userId, $year, false);

        $bestMonth = $this->statsRepository->getExtremeMonth($userId, $year);
        $worstMonth = $this->statsRepository->getExtremeMonth($userId, $year, false);

        $trophies = $this->trophyFindService->getTrophies($userId, $year.'-01-01');  
        $ppLeagues = $this->statsRepository->countPPLeagues($userId, $year);
        
        $mostUpsWith = $this->statsRepository->getUsersWithMostParticipationsWith($userId, $year);
        $mostAdjacentPositions = $this->statsRepository->getUsersWithMostAdjacentPositions($userId, $year);
        
        return $mostAdjacentPositions;
    }


}
