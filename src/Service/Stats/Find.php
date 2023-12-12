<?php

declare(strict_types=1);

namespace App\Service\Stats;

use App\Service\BaseService;
use App\Service\Trophy;
use App\Service\Match;
use App\Service\PPTournamentType;
use App\Repository\StatsRepository;

final class Find extends BaseService{
    public function __construct(
        protected StatsRepository $statsRepository,
        protected Trophy\Find $trophyFindService,
        protected Match\Find $matchFindService,
        protected PPTournamentType\Find $ppTournamentTypeFindService,
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
        foreach ($guesses as &$guess) {
            $this->addUser($guess);
            $this->addTournament($guess);
        }
        
        $match = $this->matchFindService->getOne($guesses[0]['match_id']);
        return array(
            'match' => $match,
            'guesses' => $guesses
        );
    }


    public function getPPRMStats(int $ppRoundMatchId){
        $aggregates = $this->statsRepository->getPPRMAggregates($ppRoundMatchId);
        $stats = array(
            "most_locked" => $this->statsRepository->getCommonLock($ppRoundMatchId),
            "preso_count" => isset($aggregates['preso_count']) ? $aggregates['preso_count'] : null,
            "points_avg" => isset($aggregates['points_avg']) ? $aggregates['points_avg'] : null,
        );
        return $stats;
    }


    private function addTournament(&$guess){
        $guess['ppTournamentType'] = $this->ppTournamentTypeFindService->getFromPPRoundMatch($guess['ppRoundMatch_id']);
    }


    private function addUser(&$userStat){
        $userStat['user'] = array(
            "username" => $userStat['username'],
            "id" => $userStat['user_id'],
            "trophies" => $this->trophyFindService->getTrophies($userStat['user_id'])
        );
    }

    public function getWrapped(int $userId){
        $wrapped= $this->statsRepository->getWrapped($userId);
        $wrapped['most_adjacent_ups'] = json_decode($wrapped['most_adjacent_ups']);
        $wrapped['ppl_most_kind_ups'] = json_decode($wrapped['ppl_most_kind_ups']);
        $wrapped['trophy_list'] = json_decode($wrapped['trophy_list']);
        return $wrapped;
    }


}
