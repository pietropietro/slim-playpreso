<?php

declare(strict_types=1);

namespace App\Service\League;

use App\Service\RedisService;
use App\Repository\LeagueRepository;
use App\Repository\PPTournamentTypeRepository;
use App\Repository\MatchRepository;

final class Find  extends Base{
    public function __construct(
        protected RedisService $redisService,
        protected PPTournamentTypeRepository $ppTournamentTypeRepository,
        protected LeagueRepository $leagueRepository,
        protected MatchRepository $matchRepository,
    ){}

    public function adminGetAll(){
        $leagues =  $this->leagueRepository->adminGet();
        foreach ($leagues as &$league) {
            $league['nextWeeks'] = $this->hasMatchesForNextWeeks($league['id'], 4);
        }
        return $leagues;
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

    public function getNeedData(?bool $havingGuesses = false, ?string $fromTime = null): array{
        return $this->leagueRepository->getNeedData($havingGuesses, $fromTime) ?? [];
    }


    //TODO move in PPTT
    public function getForPPTournamentType(int $ppTTid){
        $ppTT =  $this->ppTournamentTypeRepository->getOne($ppTTid);
        
        if($ppTT['pick_league']){
            return $this->leagueRepository->getWithChildren($ppTT['pick_league']);
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


    public function hasMatchesForNextWeeks(int $id, int $weeks){

        if (self::isRedisEnabled() === true && $cached = $this->getHasMatchesForNextWeeksFromCache($id, $weeks)) {
            return $cached;
        } 

        $result = [];
        $totdays = $weeks * 7;

        for($i=7; $i<=$totdays; $i+=7){
            $startDateString = date("Y-m-d", strtotime(sprintf("%+d", ($i - 7)).' days'));
            $endDateString = date("Y-m-d", strtotime(sprintf("%+d", $i).' days'));
            $matches = $this->matchRepository->adminGet(
                leagueId: $id,
                from: $startDateString,
                to: $endDateString
            );
            array_push( $result, (int)!!$matches);
        }
        $this->saveHasMatchesForNextWeeksInCache($id, $weeks, $result);
        return $result;
    }


}