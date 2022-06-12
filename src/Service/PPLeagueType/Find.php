<?php

declare(strict_types=1);

namespace App\Service\PPLeagueType;

use App\Service\RedisService;
use App\Repository\PPLeagueTypeRepository;
use App\Repository\UserParticipationRepository;
use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPLeagueTypeRepository $ppLeagueTypeRepository,
        protected UserParticipationRepository $userParticipationRepository,
    ){}

    public function getOne(int $ppLeagueTypeId){
        return $this->ppLeagueTypeRepository->getOne($ppLeagueTypeId);
    }

    public function getAvailable(int $userId) 
    {
        //TODO REDIS THIS
        // if (self::isRedisEnabled() === true && $cached = $this->getAvailablePPLeagueTypesFromCache($userId)) {
        //     return $cached;
        // } 

        $ppLTypesMap = $this->ppLeagueTypeRepository->getMap();
        $promotedPPLTIds = $this->userParticipationRepository->getPromotedPPLeagueTypeIds($userId);
        $currentPPLTIds = $this->userParticipationRepository->getCurrentPPLeagueTypeIds($userId);

        $toRetrieveList = [];

        foreach($ppLTypesMap as $typeKey => $typeItem){
            $IdsOfType = explode(',', $typeItem['ppLTIds']);

            if(!!$currentPPLTIds && !empty(array_intersect($currentPPLTIds, $IdsOfType ))){
                unset($ppLTypesMap[$typeKey]);
                continue;
            }

            $okIds = !!$promotedPPLTIds ? array_values(array_diff($IdsOfType, $promotedPPLTIds)) : $IdsOfType;
            $difference = count($IdsOfType) - count($okIds);
            array_push($toRetrieveList, $okIds[0]);
        }
        return  $this->ppLeagueTypeRepository->get($toRetrieveList);
    }

}