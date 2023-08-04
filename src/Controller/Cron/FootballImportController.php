<?php

declare(strict_types=1);

namespace App\Controller\Cron;

use Slim\Http\Request;
use Slim\Http\Response;

final class FootballImportController extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
    ): Response {
        $this->getGuessVerifyService()->setMissed();

        $leagues = [];

        if(isset($request->getQueryParams()['future'])){
            $leagues = $this->getLeaguesService()->getNeedFutureData();
        } 
        else {
            $havingGuesses = true;
            if(isset($request->getQueryParams()['havingGuesses']) != null){
                $havingGuesses = (bool) $request->getQueryParams()['havingGuesses'];
            }
            $fromTime = $request->getQueryParams()['fromTime'] ?? null;
            $leagues = $this->getLeaguesService()->getNeedPastData($havingGuesses, $fromTime);
        }

        foreach ($leagues as $key => $league) {
            if(!$league['ls_suffix'])continue;
            $this->getImportLeagueDataService()->fetch($league['ls_suffix'], $league['id']);
        }

        $message = date('H:i:s T'). (count($leagues) > 0 ? ' count:'.count($leagues).',like: '.$leagues[0]['ls_suffix'] : ' - no leagues');
        
        return $this->jsonResponse($response, 'success', $message, 200);
    }
}
