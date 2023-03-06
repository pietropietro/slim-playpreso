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


    public function getForPPTournamentType(int $ppTTid, bool $id_only = false){
        $ppTT =  $this->ppTournamentTypeRepository->getOne($ppTTid);

        if($ppTT['cup_format'] && $ppTT['name'] === 'World Cup'){
           return $this->leagueRepository->getForArea('world', null, $id_only);
        }
        if($ppTT['cup_format']) return [];

        $area_ptt = array('Europe', 'America', 'Asia', 'Africa');
        
        if(in_array($ppTT['name'], $area_ptt)){
            $areaLeagues = $this->leagueRepository->getForArea(strtolower($ppTT['name']), $ppTT['level']);
            if(!$id_only) return $areaLeagues;
            return array_column($areaLeagues, 'id');
        }       

        $country = $ppTT['name'] === 'Random' ? null : strtolower($ppTT['name']);
        return $this->leagueRepository->getForCountry($country, $ppTT['level'], $id_only);
    }
}