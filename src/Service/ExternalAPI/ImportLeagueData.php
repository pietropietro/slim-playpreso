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
        protected League\Update $leagueUpdateService,
        protected Team\Create $teamCreateService,
        protected HttpClientService $httpClientService
    ){}

    public function fetchOne(string $ls_suffix, int $leagueId){

        try {
            $req_url = $this->buildUrl($ls_suffix);           
            $response = $this->httpClientService->getSync($req_url);
            $statusCode = $response->getStatusCode();

            if ($statusCode  >= 200 && $statusCode  < 300) {
                $decoded = json_decode((string)$response->getBody());
            } else if($statusCode == 410){
                $this->setLeague410($leagueId);
            }
        } catch (\Throwable $t) {
            error_log($t->getMessage());
        }

        if(!isset($decoded)) return;
        return $this->elaborateResponse($decoded, $leagueId);
    }


    public function buildUrl(string $ls_suffix){
        if(!$ls_suffix)return;
        $utc_plus = $this->isDaylightSavingTime() ? 2 : 1;
        $req_url = $_SERVER['EXTERNAL_API_BASE_URI'].$ls_suffix.'/'.$utc_plus;
        return $req_url;
    }


    public function elaborateResponse($decodedResponse, $leagueId){
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

    private function setLeague410(int $leagueId){
        $data = array(
            "ls_410" => 1
        );
        $this->leagueUpdateService->update($leagueId, $data);
    }
    
}