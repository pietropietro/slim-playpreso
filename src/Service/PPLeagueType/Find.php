<?php

declare(strict_types=1);

namespace App\Service\PPLeagueType;

use App\Service\RedisService;
use App\Repository\PPLeagueTypeRepository;
use App\Repository\UserParticipationRepository;
use App\Service\User\Points;
use App\Service\League;
use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPLeagueTypeRepository $ppLeagueTypeRepository,
        protected UserParticipationRepository $userParticipationRepository,
        protected Points $pointsService,
        protected League\Find $leagueService,
    ){}

    public function getOne(int $id){
        $ppLT =  $this->ppLeagueTypeRepository->getOne($id);
        $ppLT['leagues'] = $this->leagueService->getForPPLT($id);
        return $ppLT;
    }

    public function getAvailable(int $userId) 
    {
        $ids = $this->getAvailableIds($userId);
        if(!$ids) return [];
        return  $this->ppLeagueTypeRepository->get($ids);
    }

    public function getAvailableIds(int $userId){

        $ppLTypesMap = $this->ppLeagueTypeRepository->getMap();
        $promotedPPLTIds = $this->userParticipationRepository->getPromotedPPLeagueTypeIds($userId);
        $currentPPLTIds = $this->userParticipationRepository->getCurrentPPLeagueTypeIds($userId);

        $ids = [];

        foreach($ppLTypesMap as $typeKey => $typeItem){
            $idsOfType = explode(',', $typeItem['ppLTIds']);

            if(!!$currentPPLTIds && !empty(array_intersect($currentPPLTIds, $idsOfType ))){
                unset($ppLTypesMap[$typeKey]);
                continue;
            }

            $okIds = !!$promotedPPLTIds ? array_values(array_diff($idsOfType, $promotedPPLTIds)) : $idsOfType;
            $difference = count($idsOfType) - count($okIds);
            array_push($ids, $okIds[0]);
        }
        
        return $this->filterIdsExpensive($userId, $ids);
    }

    public function filterIdsExpensive(int $userId, array $ids){
        $userPoints = $this->pointsService->get($userId);
        return $this->ppLeagueTypeRepository->filterIdsExpensive($ids, $userPoints);
    }

    public function canAfford(int $userId, int $typeId){
        $userPoints = $this->pointsService->get($userId);
        $cost = $this->ppLeagueTypeRepository->getOne($typeId)['cost'];
        return $userPoints >= $cost;
    }

    public function isAllowed($userId, $typeId){
        $okIds = $this->getAvailableIds($userId);
        return in_array($typeId, $okIds);
    }

}