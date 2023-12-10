<?php

declare(strict_types=1);

namespace App\Service\Stats;

use App\Service\BaseService;
use App\Service\Trophy;
use App\Service\Stats;
use App\Repository\UserParticipationRepository;
use App\Repository\UserRepository;
use App\Repository\StatsRepository;
use App\Repository\PPTournamentTypeRepository;

final class CalculateYearWrapped extends BaseService{
    public function __construct(
        protected StatsRepository $statsRepository,
        protected UserParticipationRepository $userParticipationRepository,
        protected UserRepository $userRepository,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
        protected Trophy\Find $trophyFindService,
        protected Stats\FindAdjacentUps $statsFindAdjacentUpsService,
    ) {}

    public function start(){
        $year = 2023;
        $ids = $this->userRepository->getValue('id');
        foreach ($ids as $id) {
            $this->getData($id, $year);
        }
        echo('ciao');
    }

    private function getData(int $userId, int $year){
        $dataArray = array();
        $dataArray['user_id'] = $userId;

        $mainSummary = $this->statsRepository->getUserMainSummary($userId, $year);
        if(!isset($mainSummary['tot_locks']) || $mainSummary['tot_locks'] < 50) return;
        $dataArray = array_merge($dataArray, $mainSummary);

        $pplStats = $this->getPPLStats($userId, $year);
        if(!$pplStats ) return;
        $dataArray = array_merge($dataArray, $pplStats);

        $dataArray = array_merge($dataArray, $this->statsRepository->getCommonLock(null, $userId, $year));
        $dataArray = array_merge($dataArray, $this->statsRepository->getUserMissedCount($userId,$year));
        
        $dataArray = array_merge($dataArray, $this->getCommonTeamsData($userId, $year));
        $dataArray = array_merge($dataArray, $this->getHighestTeamsData($userId, $year));
        
        $dataArray = array_merge($dataArray, $this->getCommonLeagueData($userId,$year));
        $dataArray = array_merge($dataArray, $this->getHighestLeagueData($userId,$year));

        $dataArray = array_merge($dataArray, $this->getBestMonthData($userId,$year));
        $dataArray = array_merge($dataArray, $this->getWorstMonthData($userId,$year));

        $trophies = $this->trophyFindService->getTrophies($userId, $year.'-01-01');  
        $dataArray['trophy_tot'] = count($trophies);
        $dataArray['trophy_list'] = json_encode($trophies);
        
        $dataArray = array_merge($dataArray, $this->statsRepository->getUsersWithMostParticipationsWith($userId, $year)[0]);
        $dataArray = array_merge($dataArray, $this->statsFindAdjacentUpsService->getUsersWithMostAdjacentPositions($userId, $year));

        $this->statsRepository->saveWrapped($dataArray);
    }

    private function getBestMonthData(int $userId, int $year){
        $data = $this->statsRepository->getExtremeMonth($userId, $year);
        $returnData = array();
        $returnData['best_month'] = $data['month'];
        $returnData['best_month_tot_preso'] = $data['tot_preso'];
        $returnData['best_month_tot_locks'] = $data['tot_locks'];
        $returnData['best_month_avg_points'] = $data['avg_points'];
        return $returnData;
    }
    
    private function getWorstMonthData(int $userId, int $year){
        $data = $this->statsRepository->getExtremeMonth($userId, $year, false);
        $returnData = array();
        $returnData['worst_month'] = $data['month'];
        $returnData['worst_month_tot_preso'] = $data['tot_preso'];
        $returnData['worst_month_tot_locks'] = $data['tot_locks'];
        $returnData['worst_month_avg_points'] = $data['avg_points'];
        return $returnData;
    }

    private function getCommonTeamsData(int $userId, int $year){
        $data = $this->statsRepository->getUserCommonTeams($userId, $year);
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

    private function getHighestTeamsData(int $userId, int $year){
        $data = $this->statsRepository->getUserHighestAverageTeams($userId, $year);
        if(!is_array($data)|| count($data)==0){
            return [];
        }
        $data = $data[0];
        $returnData = array();
        $returnData['high_team_id'] = $data['id'];
        $returnData['high_team_name'] = $data['name'];
        $returnData['high_team_tot_locks'] = $data['tot_locks'];
        $returnData['high_team_avg_points'] = $data['avg_points'];
        // $returnData['high_team_tot_preso'] = $data['tot_preso'];
        return $returnData;
    }

    private function getCommonLeagueData(int $userId, int $year){
        $data = $this->statsRepository->getUserLeagues($userId, $year)[0];
        $returnData = array();
        $returnData['most_league_id'] = $data['id'];
        $returnData['most_league_name'] = $data['name'];
        $returnData['most_league_country'] = $data['country'];
        $returnData['most_league_tot_locks'] = $data['tot_locks'];
        $returnData['most_league_avg_points'] = $data['avg_points'];
        return $returnData;
    }

    private function getHighestLeagueData(int $userId, int $year){
        $data = $this->statsRepository->getUserLeagues($userId, $year,false)[0];
        $returnData = array();
        $returnData['high_league_id'] = $data['id'];
        $returnData['high_league_name'] = $data['name'];
        $returnData['high_league_country'] = $data['country'];
        $returnData['high_league_tot_locks'] = $data['tot_locks'];
        $returnData['high_league_avg_points'] = $data['avg_points'];
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
