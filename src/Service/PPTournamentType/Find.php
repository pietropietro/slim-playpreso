<?php

declare(strict_types=1);

namespace App\Service\PPTournamentType;

use App\Service\RedisService;
use App\Repository\PPTournamentTypeRepository;
use App\Repository\UserParticipationRepository;
use App\Service\Points;
use App\Service\League;
use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
        protected UserParticipationRepository $userParticipationRepository,
        protected Points\Find $pointsService,
        protected League\Find $leagueService,
    ){}

    public function getOne(int $id){
        $ppTT =  $this->ppTournamentTypeRepository->getOne($id);
        $ppTT['leagues'] = $this->leagueService->getForPPTournamentType($id);
        if(!$ppTT['cup_format']){
            $ppTT['next'] = $this->ppTournamentTypeRepository->getByNameAndLevel(name: $ppTT['name'], level: $ppTT['level']+1);
        }
        else{
            $ppTT['cup_format'] = json_decode($ppTT['cup_format']);
        }
        return $ppTT;
    }

    public function get(array $ids){
        $ppTTs =  $this->ppTournamentTypeRepository->get($ids);
        foreach ($ppTTs as $key => $tt) {
            $ppTTs[$key]['leagues'] = $this->leagueService->getForPPTournamentType($tt['id']);
        }
        return $ppTTs;
    }

    public function getAvailablePPCupsForUser(int $userId): array{
        return $this->ppTournamentTypeRepository->availablePPCupsForUser($userId);
    }

    public function getAvailablePPLeaguesForUser(int $userId, bool $only_ids = true): array{

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
        if($only_ids) return $ids ?? [];
        return $ids ? $this->get($ids) : [];
    }

    //TODO add to check service
    public function filterIdsExpensive(int $userId, array $ids){
        if(!$ids)return null;
        $userPoints = $this->pointsService->get($userId);
        return $this->ppTournamentTypeRepository->filterIdsExpensive($ids, $userPoints);
    }

}