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
        $aggregates = $this->statsRepository->bestUsers();

        foreach ($aggregates as &$value) {
            $this->addUser($value);
        }

        $returnArray = array(
            "best" => $aggregates,
        );

        if($userId && !in_array($userId, array_column($aggregates,'user_id'))){
            $userResult = $this->statsRepository->bestUsers($userId);
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
