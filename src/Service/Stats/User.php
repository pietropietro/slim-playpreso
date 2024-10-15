<?php

declare(strict_types=1);

namespace App\Service\Stats;

use App\Service\BaseService;
use App\Service\RedisService;
use App\Service\Guess;
use App\Service\PPRound;
use App\Repository\StatsRepository;

final class User extends BaseService{
    public function __construct(
        protected StatsRepository $statsRepository,
        protected RedisService $redisService,
        protected Guess\Find $guessFindService,
        protected PPRound\Find $ppRoundFindService,
    ) {}

    private const REDIS_KEY_STATS = 'stats_user:%d:from:%s:to:%s';

    public function getForUser(int $userId, ?string $from = null, ?string $to=null){

        if (self::isRedisEnabled() === true ) {
            $redisKey = sprintf(self::REDIS_KEY_STATS, $userId, $from ?? 'null', $to ?? 'null');
            $cachedStats = $this->redisService->get($redisKey); // This returns null if not found or the user data if found
            if ($cachedStats !== null) {
                return $cachedStats;
            }
        } 

        $mainStats = $this->statsRepository->getUserMainSummary($userId, $from, $to);
        if(!$mainStats)return null;
        $missedStats = $this->statsRepository->getUserMissedCount($userId, $from, $to);
        $commonLock = $this->statsRepository->getCommonLock(null, $userId, $from, $to);

        $mainStats['tot_missed'] = $missedStats['tot_missed'];
        $mainStats['commonLock'] = $commonLock;

  
        $bestLeagues =  $this->statsRepository->getUserLeagues($userId, $from, $to, 1);
        foreach($bestLeagues as &$leagueStat){
            $leagueStat['guesses'] = $this->guessFindService->getForLeague($leagueStat['id'], $userId, $from, $to);
        }
        // $worstLeagues =  $this->statsRepository->getUserLeagues($userId, $from, $to, 2);
        // foreach($worstLeagues as &$leagueStat){
        //     $leagueStat['guesses'] = $this->guessFindService->getForLeague($leagueStat['id'], $userId, $from, $to);
        // }

        $bestTeams =  $this->statsRepository->getUserExtremeAverageTeams($userId, $from, $to, true);
        foreach($bestTeams as &$teamStat){
            $teamStat['guesses'] = $this->guessFindService->getForTeam($teamStat['id'], $userId, $from, $to);
        }

        // $worstTeams =  $this->statsRepository->getUserExtremeAverageTeams($userId, $from, $to, false);
        // foreach($worstTeams as &$teamStat){
        //     $teamStat['guesses'] = $this->guessFindService->getForTeam($teamStat['id'], $userId, $from, $to);
        // }

        $fullPresoRounds = $this->ppRoundFindService->getFullPresoRound($userId,null, $from, $to);

        $stats = array(
            'mainStats' => $mainStats,
            'fullPresoRounds' => $fullPresoRounds,
            'leagues' => array(
                'common' => $this->statsRepository->getUserLeagues($userId, $from, $to, 0),
                'best' => $bestLeagues,
                // 'worst' => $worstLeagues
            ),
            'teams' => array(
                'common' => $this->statsRepository->getUserCommonTeams($userId, $from, $to),
                'best' => $bestTeams,
                // 'worst' => $worstLeagues
            )
        );

        if (self::isRedisEnabled() === true ) {
            $expiration = 12 * 60 * 60; // 12 hours in seconds
            $this->redisService->setex($redisKey, $stats, $expiration);
        }
        return $stats;
       
    }

    public function getUserMainSummary(int $userId, ?string $from = null, ?string $to=null){
        return $this->statsRepository->getUserMainSummary($userId, $from, $to);
    }

    public function getUserMissedCount(int $userId, ?string $from = null, ?string $to=null){
        return $this->statsRepository->getUserMissedCount($userId, $from, $to);
    }



}
