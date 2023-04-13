<?php

declare(strict_types=1);

namespace App\Service\PPTournamentType;

use App\Service\RedisService;
use App\Repository\PPTournamentTypeRepository;
use App\Repository\UserParticipationRepository;
use App\Service\Points;
use App\Service\League;
use App\Service\Trophies;
use App\Service\BaseService;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
        protected UserParticipationRepository $userParticipationRepository,
        protected Points\Find $pointsService,
        protected League\Find $leagueService,
        protected Trophies\Find $trophiesFindService,
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

    public function getPreviousLevel(int $id){ 
        $pptt = $this->getOne($id, false);
        return $this->ppTournamentTypeRepository->getByNameAndLevel($pptt['name'], $pptt['level'] - 1);
    }


    public function getNextLevel(int $id){ 
        $pptt = $this->getOne($id, false);
        return $this->ppTournamentTypeRepository->getByNameAndLevel($pptt['name'], $pptt['level'] + 1);
    }

    private function enrich($ppTT){
        $ppTT['leagues'] = $this->leagueService->getForPPTournamentType($ppTT['id']);
        if(!$ppTT['cup_format']){
            // TODO pptt specific values for promotions / relegations
            $ppTT['promote'] = (int) $_SERVER['PPLEAGUE_PROMOTIONS'];
            $ppTT['relegate'] = $ppTT['level'] > 1 ? (int) $_SERVER['PPLEAGUE_RELEGATIONS'] : null;
            $ppTT['rejoin'] = 2;
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

    public function getCloseToStart(array $ids){
        return $this->ppTournamentTypeRepository->getCloseToStart($ids);
    }

    public function getFromPPRoundMatch(int $ppRoundMatchId){
        $pptt = $this->ppTournamentTypeRepository->getFromPPRoundMatch($ppRoundMatchId);
        if($pptt)return $pptt;
        return $this->ppTournamentTypeRepository->getMOTDType();
    }

    public function getMOTDType(){
        return $this->ppTournamentTypeRepository->getMOTDType();
    }

    public function getUps(int $id, ?int $user_id=null, ?int $limit=1){
        //redis logic here todo
        $stats = $this->ppTournamentTypeRepository->getUps($id, $user_id, $limit);
        if($stats){
            foreach ($stats as &$s) {
                $s['user'] = array(
                    'id' => $s['user_id'],
                    'username' => $s['username'],
                    'trophies' => $this->trophiesFindService->getTrophies($s['user_id'])
                );
            }
        }
        return $stats;
    }

}