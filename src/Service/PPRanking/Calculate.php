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
        $points_from_ppl = $this->ppRankingRepository->fetchPointsFromPPLeagues(); //total_trophy_points
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
            $points = $item['total_trophy_points'];
            if (!isset($total_points[$userId])) {
                $total_points[$userId] = 0;
            }
            $total_points[$userId] += $points;
        }

        // Combine and sum points from PP Cups.
        foreach ($points_from_ppc as $item) {
            $userId = $item['user_id'];
            $points = $item['points']; // Assuming this is already summed in the repository method.
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
    
}