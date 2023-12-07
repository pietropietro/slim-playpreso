<?php

declare(strict_types=1);

namespace App\Service\Stats;

use App\Service\BaseService;
use App\Service\Trophy;
use App\Service\Stats;
use App\Repository\UserParticipationRepository;
use App\Repository\StatsRepository;
use App\Repository\PPTournamentTypeRepository;

final class CalculateYearWrapped extends BaseService{
    public function __construct(
        protected StatsRepository $statsRepository,
        protected UserParticipationRepository $userParticipationRepository,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
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
        
        $returnArr['pplStats'] = $this->getPPLStats($userId, $year);
        
        $returnArr['mostUpsWith'] = $this->statsRepository->getUsersWithMostParticipationsWith($userId, $year);
        $returnArr['mostAdjacentPositions'] = $this->statsFindAdjacentUpsService->getUsersWithMostAdjacentPositions($userId, $year);
        
        return $returnArr;
    }

    private function getPPLStats(int $userId, int $year){
        $ppLeaguesParticipations = $this->userParticipationRepository->getForUser(
            $userId, 'ppLeague_id', true, null, null, $year.'-01-01', $year.'-12-31'
        );
        
        if(!is_array($ppLeaguesParticipations)) return null;
        
        $pplArr = array();
        $pplArr['count'] = count($ppLeaguesParticipations);
        $pplArr['most_ppt_kind'] = $this->statsRepository->mostPPLeagueParticipations($userId, $year);
        $mostPPTKindUps= $this->userParticipationRepository->get( 
            explode(',', $pplArr['most_ppt_kind']['ups_ids'])
        );
        foreach($mostPPTKindUps as &$up){
            $up['ppTournamentType'] = $this->ppTournamentTypeRepository->getOne($up['ppTournamentType_id']);
        }
        $pplArr['most_ppt_kind']['ups'] = $mostPPTKindUps;
        
        return $pplArr;
    }


}
