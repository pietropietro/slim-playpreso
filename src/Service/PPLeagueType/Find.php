<?php

declare(strict_types=1);

namespace App\Service\PPLeagueType;

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
        $ppLT =  $this->ppTournamentTypeRepository->getOne($id);
        $ppLT['leagues'] = $this->leagueService->getForPPLeagueType($id);
        return $ppLT;
    }

    public function getAvailable(int $userId) 
    {
        $ids = $this->getAvailableTournamentsForUser($userId, true);
        if(!$ids) return [];
        return  $this->ppTournamentTypeRepository->get($ids);
    }

    //TODO integrate cups
    public function getAvailableTournamentsForUser(int $userId, bool $only_ids = true, bool $get_cups){


        $tournamentTypesMap = $this->ppTournamentTypeRepository->getPPLeaguesMap();
        $promotedTTids = $this->userParticipationRepository->getPromotedTournamentTypesForUser($userId, false, true);
        $currentTTids = $this->userParticipationRepository->getCurrentTournamentTypesForUser($userId, false, true);

        $ids = [];

        foreach($tournamentTypesMap as $typeKey => $typeItem){
            $sameNameTournamentIds = explode(',', $typeItem['ppLTIds']);

            if(!!$currentTTids && !empty(array_intersect($currentTTids, $sameNameTournamentIds ))){
                unset($tournamentTypesMap[$typeKey]);
                continue;
            }

            $okIds = !!$promotedTTids ? array_values(array_diff($sameNameTournamentIds, $promotedTTids)) : $sameNameTournamentIds;
            $difference = count($sameNameTournamentIds) - count($okIds);
            array_push($ids, $okIds[0]);
        }
        
        return $this->filterIdsExpensive($userId, $ids);
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
        $okIds = $this->getAvailableTournamentsForUser($userId, true);
        return in_array($typeId, $okIds);
    }

}