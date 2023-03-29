<?php

declare(strict_types=1);

namespace App\Service\Trophies;

use App\Service\BaseService;
use App\Service\RedisService;
use App\Repository\UserParticipationRepository;
use App\Repository\PPTournamentTypeRepository;

final class Find extends BaseService{

    public function __construct(
        protected RedisService $redisService,
        protected UserParticipationRepository $userParticipationRepository,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
    ){}

    public function getTrophies(int $userId){
        $ppLeagueUps = $this->userParticipationRepository->getForUser(
            $userId, 'ppLeague_id', started: null, finished: true, minPosition: 1
        );  

        //TODO
        $ppCupWins = $this->userParticipationRepository->getCupWins($userId);

        $trophiesUP = array_merge($ppLeagueUps, $ppCupWins);
        foreach ($trophiesUP as &$trophyUP) {
            $trophyUP['ppTournamentType'] = $this->ppTournamentTypeRepository->getOne($trophyUP['ppTournamentType_id']);
        }
        
        return $trophiesUP;
    }

}
