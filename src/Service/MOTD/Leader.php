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
        return $this->motdRepository->retrieveMotdChart( $offset, $limit);
    }

}