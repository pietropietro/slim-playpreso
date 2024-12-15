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

    public function start(int $year){
        $ids = $this->statsRepository->getUsersWithGuessInYear($year, 30);
        foreach ($ids as $id) {
            $this->getData($id, $year);
        }
    }

    private function getData(int $userId, int $year){
        $dataArray = array();
        $dataArray['user_id'] = $userId;
        $dataArray['stats_year'] = $year;

        // Define the start and end dates for the year
        $from = "{$year}-01-01"; // First day of the year
        $to = "{$year}-12-31";   // Last day of the year

        $mainSummary = $this->statsRepository->getUserMainSummary($userId, $from, $to);
        if(!isset($mainSummary['tot_locks']) || $mainSummary['tot_locks'] < 50) return;
        $dataArray = array_merge($dataArray, $mainSummary);

        $pplStats = $this->getPPLStats($userId, $year);
        if(!$pplStats ) return;
        $dataArray = array_merge($dataArray, $pplStats);

        $commonLock= $this->statsRepository->getCommonLock(null, $userId, $from, $to, 1);
        if($commonLock){
            $dataArray = array_merge($dataArray, $commonLock[0]);
        }
        $dataArray = array_merge($dataArray, $this->statsRepository->getUserMissedCount($userId,$from, $to));
        
        $dataArray = array_merge($dataArray, $this->getCommonTeamsData($userId, $from, $to));
        $dataArray = array_merge($dataArray, $this->getExtremeTeamsData($userId, $from, $to, true));
        $dataArray = array_merge($dataArray, $this->getExtremeTeamsData($userId, $from, $to, false));
        
        $dataArray = array_merge($dataArray, $this->getExtremeLeagueData($userId, $from, $to, 0));
        $dataArray = array_merge($dataArray, $this->getExtremeLeagueData($userId, $from, $to, 1));
        $dataArray = array_merge($dataArray, $this->getExtremeLeagueData($userId, $from, $to, 2));

        $bestMonth= $this->getBestMonthData($userId,$year);
        if($bestMonth){
            $dataArray = array_merge($dataArray, $bestMonth);
        }

        $worstMonth = $this->getWorstMonthData($userId,$year);
        if($worstMonth){
            $dataArray = array_merge($dataArray, $worstMonth);
        }

        $trophies = $this->trophyFindService->getTrophies($userId, $year.'-01-01');  
        $dataArray['trophy_tot'] = count($trophies);
        $dataArray['trophy_list'] = json_encode($trophies);
        
        $dataArray = array_merge($dataArray, $this->statsRepository->getUsersWithMostParticipationsWith($userId, $year)[0]);
        $dataArray = array_merge($dataArray, $this->statsFindAdjacentUpsService->getUsersWithMostAdjacentPositions($userId, $year));

        $this->statsRepository->saveWrapped($dataArray);
    }

    private function getBestMonthData(int $userId, int $year){
        $data = $this->statsRepository->getExtremeMonth($userId, $year);
        if(!$data) return;
        $returnData = array();
        $returnData['best_month'] = $data['month'];
        $returnData['best_month_tot_preso'] = $data['tot_preso'];
        $returnData['best_month_tot_locks'] = $data['tot_locks'];
        $returnData['best_month_avg_points'] = $data['avg_points'];
        return $returnData;
    }
    
    private function getWorstMonthData(int $userId, int $year){
        $data = $this->statsRepository->getExtremeMonth($userId, $year, false);
        if(!$data) return;
        $returnData = array();
        $returnData['worst_month'] = $data['month'];
        $returnData['worst_month_tot_preso'] = $data['tot_preso'];
        $returnData['worst_month_tot_locks'] = $data['tot_locks'];
        $returnData['worst_month_avg_points'] = $data['avg_points'];
        return $returnData;
    }

    private function getCommonTeamsData(int $userId, string $from, string $to){
        $data = $this->statsRepository->getUserCommonTeams($userId, $from, $to);
        if(!is_array($data)|| count($data) == 0){
            return [];
        }
        $data = $data[0];
        $returnData = array();
        $returnData['most_team_id'] = $data['id'];
        $returnData['most_team_name'] = $data['name'];
        $returnData['most_team_tot_locks'] = $data['tot_locks'];
        $returnData['most_team_avg_points'] = $data['avg_points'];
        // $returnData['most_team_tot_preso'] = $data['tot_preso'];
        return $returnData;
    }

    private function getExtremeTeamsData(int $userId, string $from, string $to, bool $bestWorstFlag = true){
        $data = $this->statsRepository->getUserExtremeAverageTeams($userId, $from, $to, $bestWorstFlag);
        if(!is_array($data)|| count($data)==0){
            return [];
        }
        $data = $data[0];

        $prefix = $bestWorstFlag ? 'high' : 'low';
        $returnData = array();
        $returnData[$prefix.'_team_id'] = $data['id'];
        $returnData[$prefix.'_team_name'] = $data['name'];
        $returnData[$prefix.'_team_tot_locks'] = $data['tot_locks'];
        $returnData[$prefix.'_team_avg_points'] = $data['avg_points'];
        // $returnData['high_team_tot_preso'] = $data['tot_preso'];
        return $returnData;
    }

    //0 is common, 1 is highest, 2 is lowest
    private function getExtremeLeagueData(int $userId, string $from, string $to, int $commonHighLow){
        $data = $this->statsRepository->getUserLeagues($userId, $from, $to, $commonHighLow)[0];
        $prefix = $commonHighLow === 0 ? 'most' : ($commonHighLow === 1 ? 'high' : 'low');
        $returnData = array();
        $returnData[$prefix.'_league_id'] = $data['id'];
        $returnData[$prefix.'_league_name'] = $data['name'];
        $returnData[$prefix.'_league_country'] = $data['country'];
        $returnData[$prefix.'_league_tot_locks'] = $data['tot_locks'];
        $returnData[$prefix.'_league_avg_points'] = $data['avg_points'];
        return $returnData;
    }

    private function getPPLStats(int $userId, int $year){
        $ppLeaguesParticipations = $this->userParticipationRepository->getForUser(
            $userId, 'ppLeague_id', true, null, null, $year.'-01-01', $year.'-12-31'
        );
        
        if(!is_array($ppLeaguesParticipations) || count($ppLeaguesParticipations) < 2) return null;
        
        $pplArr = array();
        $pplArr['ppl_joined_tot'] = count($ppLeaguesParticipations);
        $mostPPTKind = $this->statsRepository->mostPPLeagueParticipations($userId, $year);
        $pplArr = array_merge($pplArr, $mostPPTKind);

        $mostPPTKindUps= $this->userParticipationRepository->get( 
            explode(',', $pplArr['ups_ids'])
        );

        foreach($mostPPTKindUps as &$up){
            $up['ppTournamentType'] = $this->ppTournamentTypeRepository->getOne($up['ppTournamentType_id']);
        }
        $pplArr['ppl_most_kind_ups'] = json_encode($mostPPTKindUps);
        
        unset($pplArr['ups_ids']);
        return $pplArr;
    }


}
