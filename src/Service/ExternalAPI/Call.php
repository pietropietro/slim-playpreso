<?php

declare(strict_types=1);

namespace App\Service\ExternalAPI;

use GuzzleHttp\Client;
use App\Service\BaseService;
use App\Service\Match;
use App\Service\League;
use App\Service\Team;

final class Call extends BaseService{
    public function __construct(
        protected Match\Elaborate $matchService,
        protected League\Elaborate $leagueService,
        protected Team\Elaborate $teamService,
    ){}

    public function fetchExternalData(string $ls_suffix, int $league_id, bool $add_match_suffix = false){
        //REAL FETCH
        $client = new Client(
            ['base_uri' => $_SERVER['EXTERNAL_API_BASE_URI'],
            'timeout'  => 10.0]
        );
        $req_url = $ls_suffix.'/2?MD=5';
        $response = $client->get($req_url);
        $decoded = json_decode((string)$response->getBody());

        //CACHED FETCH
        // $str = file_get_contents('/Users/pietromini/Dev/playpreso/slim-playpreso/external-api-sample.json');
        // $decoded = json_decode($str); // decode the JSON into an associative array

        $ls_league_data = $decoded->Stages[0];
        $ls_events = $ls_league_data->Events ?? null;
        $ls_league_table_teams = $ls_league_data->LeagueTable->L[0]->Tables[0]->team ?? null;

        if(!$ls_events){
            throw new \App\Exception\ExternalAPI('something went wrong', 500);
        }

        if($ls_league_table_teams)$this->teamService->insertTeams($ls_league_table_teams, country: $ls_league_data->Cnm);
        $match_import_result = $this->matchService->elaborateLsEvents($ls_events, $league_id, $add_match_suffix ? $ls_suffix : null);
        if(!$add_match_suffix)$this->leagueService->elaborateLsLeagueTable($ls_league_table_teams, $league_id);
        
        return $match_import_result;

    }
    
}