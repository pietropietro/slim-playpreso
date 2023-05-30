<?php

declare(strict_types=1);

namespace App\Service\League;

use App\Service\RedisService;
use App\Repository\LeagueRepository;
use App\Repository\PPTournamentTypeRepository;
use App\Service\BaseService;
use App\Service\Match;

final class Find  extends BaseService{
    public function __construct(
        protected RedisService $redisService,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
        protected LeagueRepository $leagueRepository,
    ){}

    public function get(){
        return $this->leagueRepository->get();
    }

    public function getOne(int $id, ?bool $withStandings=false){
        $league = $this->leagueRepository->getOne($id, $withStandings);
        if(!$withStandings) return $league;
        return $this->enrich($league);
    }

    private function enrich($league){
        $league['standings'] = $league['standings'] ? json_decode($league['standings']) : null;
        return $league;
    }

    public function getNeedData(?bool $havingGuesses = true, ?string $fromTime = null): array{
        return $this->leagueRepository->getNeedData($havingGuesses, $fromTime) ?? [];
    }


    //TODO move in PPTT
    public function getForPPTournamentType(int $ppTTid){
        $ppTT =  $this->ppTournamentTypeRepository->getOne($ppTTid);
        
        if($ppTT['pick_tournament']){
            return $ppTT['pick_tournament'];
        }
        if($ppTT['pick_country']){
            return $this->leagueRepository->getForCountry($ppTT['pick_country'], $ppTT['level']);
        }
        if($ppTT['pick_area']){
            return $this->getForArea($ppTT['pick_area'], $ppTT['level']);
        }

        return $this->leagueRepository->get(maxLevel: $ppTT['level']);       
    }

    //TODO check it returns both leagues for PPArea countries and extra tournaments
    public function getForArea($ppAreaId, ?int $level=null){
        return $this->leagueRepository->getForArea($ppAreaId, $level);
    }

    //TODO check it returns only leagues for ppArea extra tournaments.
    public function getPPAreaExtraLeagues($ppAreaId){
        return $this->leagueRepository->getPPAreaExtraLeagues($ppAreaId);
    }
}