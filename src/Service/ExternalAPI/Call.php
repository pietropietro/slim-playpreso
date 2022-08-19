<?php

declare(strict_types=1);

namespace App\Service\ExternalAPI;

use GuzzleHttp\Client;
use App\Service\BaseService;
use App\Service\Match;

final class Call extends BaseService{
    public function __construct(
        protected Match\Elaborate $matchService,
    ){}

    public function fetchExternalLeagueData($ls_suffix, $league_id){

        // $client = new Client(
        //     ['base_uri' => $_SERVER['EXTERNAL_API_BASE_URI'],
        //     'timeout'  => 10.0]
        // );

        // $req_url = $ls_suffix.'/2?MD=5';

        // $response = $client->get($req_url);
        // $decoded = json_decode((string)$response->getBody());

        //TESTING
        $str = file_get_contents('/Users/pietromini/Dev/playpreso/slim-playpreso/external-api-sample.json');
        $decoded = json_decode($str); // decode the JSON into an associative array


        $received_events = $decoded->Stages[0]->Events ?? null;

        if(!$received_events){
            throw new \App\Exception\ExternalAPI('something went wrong', 500);
        }

        return $this->matchService->elaborateLsEvents($received_events, $league_id);
    }
    

        // $req_headers = ['headers' => ['apikey' => $_SERVER['EXTERNAL_API_KEY']]];

        // $req_params = [
        //     "season_id" => $external_api_season_id,
        //     "date_from" => date('Y-m-d',strtotime("-3 days")),
        //     // "date_to" => date('Y-m-d',strtotime("+8 days"))
        //     "date_to" => date('Y-m-d')
        // ];
        
        // $req_url = 'matches?'.http_build_query($req_params);

        // $response = $client->get($req_url, $req_headers);

}