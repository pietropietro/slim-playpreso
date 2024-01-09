<?php

declare(strict_types=1);

namespace App\Service\ExternalAPI;

use GuzzleHttp;
use App\Service\BaseService;
use App\Service\Match;
use App\Service\League;
use App\Service\Team;
use App\Service\HttpClientService;

final class ImportLeagueData extends BaseService{
    public function __construct(
        protected Match\Elaborate $matchElaborateService,
        protected League\Elaborate $leagueElaborateService,
        protected Team\Create $teamCreateService,
        protected HttpClientService $httpClientService
    ){}

    public function fetch(string $ls_suffix, int $leagueId){

        $utc_plus = $this->isDaylightSavingTime() ? 2 : 1;
        $req_url = $ls_suffix.'/'.$utc_plus;
        
        try {
            $response = $this->httpClientService->get(
                $req_url, 
                ['base_uri' => $_SERVER['EXTERNAL_API_BASE_URI']]
            );
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $decoded = json_decode((string)$response->getBody());
            }
        } catch (\Throwable $t) {
            error_log($t->getMessage());
        }

        if(!isset($decoded)) return;
        return $this->elaborateResponse($decoded, $leagueId);
    }

    private function elaborateResponse($decodedResponse, $leagueId){
        $this->leagueElaborateService->setFetched($leagueId);

        $ls_league_data = $decodedResponse->Stages[0];
        $ls_events = $ls_league_data->Events ?? null;
        $ls_league_table_teams = $ls_league_data->LeagueTable->L[0]->Tables[0]->team ?? null;

        if(!$ls_events){
            return;
        }
        $match_import_result = $this->matchElaborateService->elaborateLsEvents($ls_events, $leagueId);
        if($ls_league_table_teams){
            $this->leagueElaborateService->elaborateLsLeagueTable($ls_league_table_teams, $leagueId);
        }

        return $match_import_result;
    }

    //rusty calculation of daylight saving time
    private function isDaylightSavingTime() {
        // Get the current month and day in the format 'MMDD'
        $currentDate = date('md');
      
        // Get the dates of the second Sunday in March and the first Sunday in November
        $secondSundayOfMarch = date('md', strtotime('second sunday of march'));
        $firstSundayOfNovember = date('md', strtotime('last sunday of october'));
      
        // Check if the current date is between the second Sunday in March and the first Sunday in November
        if ($currentDate >= $secondSundayOfMarch && $currentDate <= $firstSundayOfNovember) {
          return true;
        } else {
          return false;
        }
      }
    
}