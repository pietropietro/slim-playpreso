<?php

declare(strict_types=1);

namespace App\Service\PPTournamentType;

use App\Service\RedisService;
use App\Repository\PPTournamentTypeRepository;
use App\Repository\UserParticipationRepository;
use App\Service\User\Points;
use App\Service\League;
use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
        protected UserParticipationRepository $userParticipationRepository,
        protected Points $pointsService,
        protected League\Find $leagueService,
    ){}

    public function getOne(int $id){
        $ppTT =  $this->ppTournamentTypeRepository->getOne($id);
        $ppTT['leagues'] = $this->leagueService->getForPPTournamentType($id);
        return $ppTT;
    }

    public function get(array $ids){
        $ppTTs =  $this->ppTournamentTypeRepository->get($ids);
        foreach ($ppTTs as $key => $tt) {
            $ppTTs[$key]['leagues'] = $this->leagueService->getForPPTournamentType($tt['id']);
        }
        return $ppTTs;
    }

    //TODO integrate cups
    public function getAvailableForUser(int $userId, bool $only_ids = true, bool $get_cups = false){

        $tournamentTypesMap = $this->ppTournamentTypeRepository->getPPLeaguesMap();
        $promotedTTids = $this->userParticipationRepository->getPromotedTournamentTypesForUser($userId, false, true);
        $currentTTids = $this->userParticipationRepository->getCurrentTournamentTypesForUser($userId, false, true);

        $ids = [];

        foreach($tournamentTypesMap as $typeKey => $typeItem){
            $sameNameTournamentIds = explode(',', $typeItem['ppTTids']);

            if(!!$currentTTids && !empty(array_intersect($currentTTids, $sameNameTournamentIds ))){
                unset($tournamentTypesMap[$typeKey]);
                continue;
            }

            $okIds = !!$promotedTTids ? array_values(array_diff($sameNameTournamentIds, $promotedTTids)) : $sameNameTournamentIds;
            $difference = count($sameNameTournamentIds) - count($okIds);
            array_push($ids, $okIds[0]);
        }
        
        $ids = $this->filterIdsExpensive($userId, $ids);
        if($only_ids) return $ids;
        return $ids ? $this->get($ids) : [];
    }

    public function filterIdsExpensive(int $userId, array $ids){
        if(!$ids)return null;
        $userPoints = $this->pointsService->get($userId);
        return $this->ppTournamentTypeRepository->filterIdsExpensive($ids, $userPoints);
    }

    public function canAfford(int $userId, int $typeId){
        $userPoints = $this->pointsService->get($userId);
        $cost = $this->ppTournamentTypeRepository->getOne($typeId)['cost'];
        return $userPoints >= $cost;
    }

    public function isAllowed($userId, $typeId){
        $okIds = $this->getAvailableForUser($userId, true);
        return in_array($typeId, $okIds);
    }

}