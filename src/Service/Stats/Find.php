<?php

declare(strict_types=1);

namespace App\Service\Stats;

use App\Service\BaseService;
use App\Service\UserParticipation;
use App\Service\Match;
use App\Repository\StatsRepository;

final class Find extends BaseService{
    public function __construct(
        protected StatsRepository $statsRepository,
        protected UserParticipation\Find $userParticipationFindService,
        protected Match\Find $matchFindService,
    ) {}

    public function bestUsers(?int $userId = null) {
        return array(
            "bestAverage" => $this->bestAverage($userId),
            "mostPoints" => $this->mostPoints($userId)
        );
    }

    private function mostPoints(?int $userId = null) {
        $aggregates = $this->statsRepository->mostPoints();

        foreach ($aggregates as &$value) {
            $this->addUser($value);
        }

        $returnArray = array(
            "best" => $aggregates,
        );


        //TODO refactor
        if($userId && !in_array($userId, array_column($aggregates,'user_id'))){
            $userResult = $this->statsRepository->mostPoints($userId);
            if(!$userResult) return $returnArray;
            $user_extra_stats = $userResult[0];
            $this->addUser($user_extra_stats);
            $returnArray['currentUserStat'] = $user_extra_stats;
        }

        return $returnArray;
    }
    
    private function bestAverage(?int $userId = null) {
        $aggregates = $this->statsRepository->bestAverage();

        foreach ($aggregates as &$value) {
            $this->addUser($value);
        }

        $returnArray = array(
            "best" => $aggregates,
        );

        //TODO refactor
        if($userId && !in_array($userId, array_column($aggregates,'user_id'))){
            $userResult = $this->statsRepository->bestAverage($userId);
            if(!$userResult) return $returnArray;
            $user_extra_stats = $userResult[0];
            $this->addUser($user_extra_stats);
            $returnArray['currentUserStat'] = $user_extra_stats;
        }

        return $returnArray;
    }

    public function lastPreso() {
        $guesses = $this->statsRepository->lastPreso();
        foreach ($guesses as &$value) {
            $this->addUser($value);
        }
        
        $match = $this->matchFindService->getOne($guesses[0]['match_id']);
        return array(
            'match' => $match,
            'guesses' => $guesses
        );
    }


    private function addUser(&$userStat){
        $userStat['user'] = array(
            "username" => $userStat['username'],
            "id" => $userStat['user_id'],
            "trophies" => $this->userParticipationFindService->getTrophies($userStat['user_id'])
        );
    }


}
