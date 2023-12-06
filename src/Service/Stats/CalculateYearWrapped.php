<?php

declare(strict_types=1);

namespace App\Service\Stats;

use App\Service\BaseService;
use App\Service\Trophy;
use App\Service\Stats;
use App\Repository\UserParticipationRepository;
use App\Repository\StatsRepository;

final class CalculateYearWrapped extends BaseService{
    public function __construct(
        protected StatsRepository $statsRepository,
        protected UserParticipationRepository $userParticipationRepository,
        protected Trophy\Find $trophyFindService,
        protected Stats\FindAdjacentUps $statsFindAdjacentUpsService,
    ) {}

    public function calculateWrapped(){
        $year = 2023;
        $userId = 264;

        $returnArr = array();

        $returnArr['mainSummary'] = $this->statsRepository->getUserMainSummary($userId, $year);
        if(isset($returnArr['mainSummary'][0]['locks']) && $returnArr['mainSummary'][0]['locks'] < 50) return;
        $returnArr['commonLock'] = $this->statsRepository->getCommonLock(null, $userId, $year);
        $returnArr['missedCount'] = $this->statsRepository->getUserMissedCount($userId,$year);
        
        $returnArr['commonTeams'] = $this->statsRepository->getUserCommonTeams($userId, $year);
        $returnArr['highestAvgTeams'] = $this->statsRepository->getUserHighestAverageTeams($userId, $year);
        
        $returnArr['commonLeagues'] = $this->statsRepository->getUserLeagues($userId, $year);
        $returnArr['bestLeagues'] = $this->statsRepository->getUserLeagues($userId, $year, false);

        $returnArr['bestMonth'] = $this->statsRepository->getExtremeMonth($userId, $year);
        $returnArr['worstMonth'] = $this->statsRepository->getExtremeMonth($userId, $year, false);

        $returnArr['trophies'] = $this->trophyFindService->getTrophies($userId, $year.'-01-01');  
        
        $ppLeaguesParticipations = $this->userParticipationRepository->getForUser($userId, 'ppLeague_id', true, null, null, $year.'-01-01', $year.'-12-31');
        if(is_array($ppLeaguesParticipations)){
            $returnArr['ppl_ups_count'] = count($ppLeaguesParticipations);
            $returnArr['ppl_ups_most'] = $this->statsRepository->mostPPLeagueParticipations($userId, $year);
            //TODO use $returnArr['ppl_ups_most']['ppl_ids'] to retrieve ups from up repo
        }
        
        $returnArr['mostUpsWith'] = $this->statsRepository->getUsersWithMostParticipationsWith($userId, $year);
        $returnArr['mostAdjacentPositions'] = $this->statsFindAdjacentUpsService->getUsersWithMostAdjacentPositions($userId, $year);
        
        return   $returnArr['ppl_ups_most'];
    }


}
