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
        $ppTT = $this->enrich($ppTT);
        return $ppTT;
    }

    public function getOneFromPPTournament(string $tournamentTable, int $tournamentId){
        return $this->ppTournamentTypeRepository->getOneFromPPTournament($tournamentTable, $tournamentId);
    }

    public function get(?array $ids, bool $onlyCups = false, ?bool $enriched = true){
        $ppTTs =  $this->ppTournamentTypeRepository->get($ids, $onlyCups);
        
        if($enriched){
            foreach ($ppTTs as &$tt) {
                $this->enrich($tt);
            }    
        }
        return $ppTTs;
    }

    private function enrich($ppTT){
        $ppTT['leagues'] = $this->leagueService->getForPPTournamentType($ppTT['id']);
        if(!$ppTT['cup_format']){
            $ppTT['next'] = $this->ppTournamentTypeRepository->getByNameAndLevel(name: $ppTT['name'], level: $ppTT['level']+1);
        }
        else{
            $ppTT['cup_format'] = json_decode($ppTT['cup_format']);
        }
        return $ppTT;
    }



    public function getAvailablePPCupsForUser(int $userId): array{
        return $this->ppTournamentTypeRepository->availablePPCupsForUser($userId);
    }



    public function getAvailablePPLeaguesForUser(int $userId, bool $ids_only = true){
        $currentPPTournamentTypes = $this->userParticipationRepository->getCurrentTournamentTypesForUser($userId, false);
        $currentPPTTNames = array_column($currentPPTournamentTypes, 'name');

        $promotedPPTTids = $this->userParticipationRepository->getPromotedTournamentTypesForUser($userId, false, true);

        $userPoints = $this->pointsService->get($userId);
        
        $availablePPTTs = $this->ppTournamentTypeRepository->availablePPLeaguesTypes(
            $currentPPTTNames,
            $promotedPPTTids,
            $userPoints,
            $ids_only
        );

        return $ids_only ? array_column($availablePPTTs, 'id') : $availablePPTTs;
    }



    //TODO remove
    public function filterIdsExpensive(int $userId, array $ids){
        if(!$ids)return null;
        $userPoints = $this->pointsService->get($userId);
        return $this->ppTournamentTypeRepository->filterIdsExpensive($ids, $userPoints);
    }

}