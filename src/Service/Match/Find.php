<?php

declare(strict_types=1);

namespace App\Service\Match;

use App\Service\BaseService;
use App\Service\League;
use App\Service\Team;
use App\Repository\MatchRepository;

final class Find extends BaseService{
    public function __construct(
        protected MatchRepository $matchRepository,
        protected League\Find $leagueFindService,
        protected Team\Find $teamFindService,
    ) {}

    private function enrich(array $match, ?bool $withStats=true){
        $match['homeTeam'] = $match['home_id'] ? $this->teamFindService->getOne($match['home_id'], false, $withStats) : null;
        $match['awayTeam'] = $match['away_id'] ? $this->teamFindService->getOne($match['away_id'], false, $withStats) : null;
        $match['league'] = $this->leagueFindService->getOne($match['league_id'], false,$withStats);
        return $match;
    }

    public function get(array $ids){
        $matches = $this->matchRepository->get($ids);
        return $this->enrichAll($matches);
    }
    
    //withStats: league standings + teams last matches WDL
    public function getOne(
        int $id, 
        ?bool $is_external_id=false, 
        ?bool $withTeams=true, 
        ?bool $withStats=true
    ) : ?array {
        $match = $this->matchRepository->getOne($id, $is_external_id);
        return $withTeams ? $this->enrich($match, $withStats) : $match;
    }

    public function getOneByLeagueRoundAndTeams(int $leagueId, int $round, int $homeId, int $awayId){
        return $this->matchRepository->getOneByLeagueRoundAndTeams($leagueId, $round, $homeId, $awayId);
    }

    public function adminGet(?array $ids = null,
        ?string $country = null,
        ?int $leagueId = null,
        ?string $from = null,
        ?string $to = null
    ):?array {
        // Check if at least one parameter is provided
        if (is_null($ids) && is_null($country) && is_null($leagueId) && is_null($from) && is_null($to)) {
            return [];
        }
    
        // Get matches from the repository with the provided parameters
        $matches = $this->matchRepository->adminGet(
            ids: $ids,
            country: $country,
            leagueId: $leagueId,
            from: $from,
            to: $to
        );
    
        return $this->enrichAll($matches, false);
    }

    private function enrichAll($matches, ?bool $withStats=true){
        foreach ($matches as &$match) {
            $match = $this->enrich($match, $withStats);
        }
        return $matches;
    }

    public function adminGetForLeague(int $leagueId, bool $next = true){
        $matches=$this->matchRepository->getMatchesForLeagues(
            array($leagueId), 
            $next ? 0 : null, 
            $next ? null : 0, 
            $next ? 'ASC' : 'DESC', 
            10
        );
        
        return $this->enrichAll($matches);
    }

    public function hasLiveMatch(array $ids){
        return $this->matchRepository->hasLiveMatch(ids: $ids);
    }

    public function getNextMatchInPPRound(int $ppRound_id){
        $match = $this->matchRepository->getNextInPPRound($ppRound_id);
        if(!$match)return null;
        return $this->enrich($match);
    }

    public function getLastMatchInPPTournament(string $type, int $typeId){
        $match = $this->matchRepository->getLastInPPTournament($type, $typeId);
        if(!$match)return null;
        return $this->enrich($match, false);
    }

    public function getNextMatchInPPTournament(string $type, int $typeId){
        $match = $this->matchRepository->getNextInPPTournament($type, $typeId);
        if(!$match)return null;
        return $this->enrich($match, false);
    }

   

    public function isBeforeStartTime(int $id){
        return $this->matchRepository->isBeforeStartTime($id);
    }

    
}

