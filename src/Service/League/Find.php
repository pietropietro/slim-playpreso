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

    public function adminGetAll(
        ?string $country = null, 
        ?int $page = null, 
        ?int $limit = null, 
        ?bool $parentOnly = null
    ){
        $offset = ($page - 1) * $limit;
        $result = $this->leagueRepository->adminGet($country, $offset, $limit, $parentOnly);

        foreach ($result['leagues'] as &$league) {
            $league['nextWeeks'] = $this->hasMatchesForNextWeeks($league['id'], 4);
        }
        return $result;
    }

    public function adminGetCountries(): array{
        return $this->leagueRepository->adminGetCountries();
    }

    //admin flag to return ls_suffix or not
    public function getOne(int $id, ?bool $admin=false, ?bool $withStandings=false){
        $league = $this->leagueRepository->getOne($id, $admin, $withStandings);

        if(isset($league['parent_id']) && $league['parent_id'] != $league['id']){
            $league['parent'] = $this->leagueRepository->getOne($league['parent_id'], false, false);
            $league['level'] = $league['parent']['level'];
        }

        if(!$withStandings) return $league;
        return $this->enrich($league);
    }

    private function enrich($league){
        $league['standings'] = $league['standings'] ? json_decode($league['standings']) : null;
        return $league;
    }

    public function getNeedPastData(?bool $havingGuesses = false, ?string $fromTime = null): array{
        return $this->leagueRepository->getNeedPastData($havingGuesses, $fromTime) ?? [];
    }

    public function getNeedFutureData(): array{
        $leaguesNoFutureMatches = $this->leagueRepository->getNeedFutureData() ?? [];
        $leaguesWithSuspectTeamNames = $this->leagueRepository->getSuspectTeamNameLeagues() ?? [];
        $mergedLeagues = array_merge($leaguesNoFutureMatches, $leaguesWithSuspectTeamNames);

        return $mergedLeagues;
    }

    public function getSuspectTeamNameLeagues(): array{
        return $this->leagueRepository->getSuspectTeamNameLeagues() ?? [];
    }



    //TODO move in PPTT
    public function getForPPTournamentType(int $ppTTid, ?bool $onlyParents=false){
        $ppTT =  $this->ppTournamentTypeRepository->getOne($ppTTid);
        
        if($ppTT['pick_league']){
            $leagues = $this->leagueRepository->getChildren($ppTT['pick_league'], false);
        }
        else if($ppTT['pick_country']){
            $leagues = $this->leagueRepository->getForCountry($ppTT['pick_country'], $ppTT['level']);
        }
        else if($ppTT['pick_area']){
            $leagues = $this->getForArea($ppTT['pick_area'], $ppTT['level']);
        }
        else{
            $leagues = $this->leagueRepository->get(maxLevel: $ppTT['level']); 
        }
        
        if($onlyParents){
            $leagues= array_filter($leagues, function ($league) {
                return (!$league['parent_id'] || $league['id'] == $league['parent_id']);
            });
        }

        return $leagues;
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