<?php

declare(strict_types=1);

namespace App\Service\MOTD;

use App\Repository\MOTDRepository;
use App\Service\RedisService;
use App\Service\BaseService;

final class Leader  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected MOTDRepository $motdRepository,
        protected Find $motdFindService,
    ){}

    public function checkIfCalculate(int $matchId){
        $motd = $this->motdRepository->getMotd();
        if($motd && $motd['match_id'] == $matchId){
            $this->calculateLeader();
        }
    }

    private function calculateLeader(){
        $topChart = $this->motdRepository->retrieveMotdChart()['chart'];
        $this->motdRepository->insertLeader($topChart[0]['user_id'], (int) $topChart[0]['tot_points']);
    }

    
    public function getMotdLeader(){
       return $this->motdRepository->getMotdLeader();        
    }

    public function getChart(
        ?int $page = 1, 
        ?int $limit = 10, 
    ){
        $offset = ($page - 1) * $limit;
        $result = $this->motdRepository->retrieveMotdChart( $offset, $limit);

        foreach ($result['chart'] as &$chartItem) {
            // do the magic (i.e. fill the period and add zeros on missing dates)
            $chartItem['sparkline_data'] = $this->fillSparklineData($chartItem);
            unset($chartItem['concat_points']);
            unset($chartItem['concat_motd']);

            $chartItem['guesses'] = $this->motdFindService->getLastForUser($chartItem['user_id']);
        }
        return $result;
    }

    private function fillSparklineData($userChart){
        $motds = explode(',', $userChart['concat_motd']);
        $points = array_map('intval', explode(',', $userChart['concat_points']));

        // Generate a complete list of dates for the last month
        $dateAgo = new \DateTime(date("Y-m-d", strtotime('-1 month')));
        $today = new \DateTime(date("Y-m-d"));
        $period = new \DatePeriod($dateAgo, new \DateInterval('P1D'), $today->modify('+1 day'));

        $completeDates = [];
        $cumulativeDates = [];
        $cumulativePoints = 0;
        $pointsByDate = array_combine($motds, $points);

        foreach ($period as $date) {
            $dateString = $date->format("Y-m-d");
            if (isset($pointsByDate[$dateString])) {
                $cumulativePoints += $pointsByDate[$dateString];
            }

            $completeDates[$dateString] = $pointsByDate[$dateString] ?? 0;
            $cumulativeDates[$dateString] = $cumulativePoints;
        }
        
        return array(
            'cumulative' => $cumulativeDates,
            'single' => $completeDates
        );

    }

}