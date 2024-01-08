<?php

declare(strict_types=1);

namespace App\Service\ExternalAPI;

use GuzzleHttp;
use App\Service\BaseService;
use App\Service\Match;
use App\Service\League;
use App\Service\Team;

final class ImportLeagueData extends BaseService{
    public function __construct(
        protected Match\Elaborate $matchElaborateService,
        protected League\Elaborate $leagueElaborateService,
        protected Team\Create $teamCreateService,
    ){}

    public function fetch(string $ls_suffix, int $league_id){
        //REAL FETCH
        $client = new GuzzleHttp\Client([
                'base_uri' => $_SERVER['EXTERNAL_API_BASE_URI'],
                'timeout'  => 10.0,
                'proxy' => $_SERVER['PROXY_URL']
            ]
        );

        //rusty calculation of daylight saving time
        $utc_plus = $this->isDaylightSavingTime() ? 2 : 1;
        $req_url = $ls_suffix.'/'.$utc_plus;
        
        try {
            $response = $client->get($req_url);
            $decoded = json_decode((string)$response->getBody());
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $message = $e->getMessage();
            error_log($message);
            $code = $response->getStatusCode();

            // Create new object with the message and code
            $errorObject = new \stdClass();
            $errorObject->message = $message ?? 'An unexpected error occurred';
            $errorObject->code = $code ?? 500;

            return $errorObject;
        } catch (GuzzleHttp\Exception\ConnectException $e) {
            // Handle connection issues
            $message = $e->getMessage();
            error_log($message);
        
            $errorObject = new \stdClass();
            $errorObject->message = $message ?? 'Connection error';
            $errorObject->code = 0; // You can choose an appropriate code
        
            return $errorObject;
        }

        $this->leagueElaborateService->setFetched($league_id);

        $ls_league_data = $decoded->Stages[0];
        $ls_events = $ls_league_data->Events ?? null;
        $ls_league_table_teams = $ls_league_data->LeagueTable->L[0]->Tables[0]->team ?? null;


        if(!$ls_events){
            return;
        }
        $match_import_result = $this->matchElaborateService->elaborateLsEvents($ls_events, $league_id);
        if($ls_league_table_teams){
            $this->leagueElaborateService->elaborateLsLeagueTable($ls_league_table_teams, $league_id);
        }

        return $match_import_result;
    }

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