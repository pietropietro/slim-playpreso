<?php

declare(strict_types=1);

namespace App\Service\PPLeagueType;

use App\Service\RedisService;
use App\Repository\PPLeagueTypeRepository;
use App\Repository\UserParticipationRepository;
use App\Repository\UserRepository;
use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPLeagueTypeRepository $ppLeagueTypeRepository,
        protected UserParticipationRepository $userParticipationRepository,
        protected UserRepository $userRepository,
    ){}

    public function getOne(int $ppLeagueTypeId){
        return $this->ppLeagueTypeRepository->getOne($ppLeagueTypeId);
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
            $IdsOfType = explode(',', $typeItem['ppLTIds']);

            if(!!$currentPPLTIds && !empty(array_intersect($currentPPLTIds, $IdsOfType ))){
                unset($ppLTypesMap[$typeKey]);
                continue;
            }

            $okIds = !!$promotedPPLTIds ? array_values(array_diff($IdsOfType, $promotedPPLTIds)) : $IdsOfType;
            $difference = count($IdsOfType) - count($okIds);
            array_push($ids, $okIds[0]);
        }
        
        return $this->filterIdsExpensive($userId, $ids);
        return $ids;
    }

    public function filterIdsExpensive(int $userId, array $ids){
        $userPoints = $this->userRepository->getPoints($userId);
        return $this->ppLeagueTypeRepository->filterIdsExpensive($ids, $userPoints);
    }

    public function canAfford(int $userId, int $typeId){
        $userPoints = $this->userRepository->getPoints($userId);
        $cost = $this->ppLeagueTypeRepository->getOne($typeId)['cost'];
        return $userPoints >= $cost;
    }

}