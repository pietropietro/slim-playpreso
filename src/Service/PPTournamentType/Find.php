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

    public function getOne(int $id, bool $enrich = true){
        $ppTT =  $this->ppTournamentTypeRepository->getOne($id);
        if(!$enrich) return $ppTT;
        return $this->enrich($ppTT);
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

    public function getNextLevel(int $id){ 
        $pptt = $this->getOne($id, false);
        return $this->ppTournamentTypeRepository->getByNameAndLevel($pptt['name'], $pptt['level'] + 1);
    }

    private function enrich($ppTT){
        $ppTT['leagues'] = $this->leagueService->getForPPTournamentType($ppTT['id']);
        if(!$ppTT['cup_format']){
            $ppTT['promote'] = floor($ppTT['participants']/4);
            $ppTT['rejoin'] = 2;
            //TODO calculate relegation logic
            $ppTT['relegate'] = null;
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
            $userPoints        
        );

        return $ids_only ? array_column($availablePPTTs, 'id') : $availablePPTTs;
    }


}