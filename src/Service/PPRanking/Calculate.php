<?php

declare(strict_types=1);

namespace App\Service\PPRanking;

use App\Repository\PPRankingRepository;
use App\Service\BaseService;

final class Calculate extends BaseService
{

    public function __construct(
        protected PPRankingRepository $ppRankingRepository,
    ) {}



    public function calculate(){
        $points_from_guesses = $this->ppRankingRepository->fetchPointsFromGuesses(); //tot_points
        $points_from_ppl = $this->ppRankingRepository->fetchPointsFromPPLeagues(); //tot_points
        $points_from_ppc = $this->ppRankingRepository->fetchPointsFromPPCups(); //points

        // Array to hold the combined points for each user.
        $total_points = [];

        // Combine and sum points from guesses.
        foreach ($points_from_guesses as $item) {
            $userId = $item['user_id'];
            $points = $item['tot_points'];
            if (!isset($total_points[$userId])) {
                $total_points[$userId] = 0;
            }
            $total_points[$userId] += $points;
        }

        // Combine and sum points from PP Leagues.
        foreach ($points_from_ppl as $item) {
            $userId = $item['user_id'];
            $points = $item['tot_points'];
            if (!isset($total_points[$userId])) {
                $total_points[$userId] = 0;
            }
            $total_points[$userId] += $points;
        }

        // Combine and sum points from PP Cups.
        foreach ($points_from_ppc as $item) {
            $userId = $item['user_id'];
            
            $cup_format = json_decode($item['cup_format']);
            $level_format = $cup_format[$item['level'] - 1];
            
            $points = $this->getRankingPointsForPPCupPlacement(
                $level_format->name, 
                $item['position'],
                $item['cost']
            );

            if (!isset($total_points[$userId])) {
                $total_points[$userId] = 0;
            }
            $total_points[$userId] += $points;
        }

        // Optionally, you can sort the results by points.
        arsort($total_points);
        // Save the sorted rankings with position
        $this->ppRankingRepository->saveRankings($total_points, date('Y-m-d'));
        return $total_points;
    }


    public function getRankingPointsForPPCupPlacement(string $level_name, int $position, int $join_cost){
        if($level_name == 'ROUND OF 16'){
            return $join_cost / 5;
        }
        if($level_name == 'QUARTER FINALS'){
            return $join_cost / 3;
        }
        if($level_name == 'SEMI FINALS'){
            return $join_cost / 2;
        }
        if($level_name == 'FINAL'){
            if($position == 1) return $join_cost * 2;
            return $join_cost * 1;
        }
    }

    public function getRankingPointsForPPLeaguePlacement($position, $level){
        $base = 100 * $level;
        if($position == 1) return $base * 2;
        if($position == 2) return $base;
        if($position == 3) return $base * 0.5;
    }
    
}