<?php

declare(strict_types=1);

namespace App\Controller\Cron;

use Slim\Http\Request;
use Slim\Http\Response;

final class Start extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
    ): Response {


        $this->getGuessService()->setMissed();
        $leagues = $this->getLeaguesService()->getNeedData();

        //when adding new league
        // array_push($leagues, 
        //     [
        //         'id'=>13,
        //         'ls_suffix'=>'turkey/super-lig'
        //     ],
        // );

        foreach ($leagues as $key => $league) {
            if(!$league['ls_suffix'])continue;
            $this->getExternalApiService()->fetchExternalData($league['ls_suffix'], $league['id'], $league['use_match_ls_suffix']);
        }

        return $this->jsonResponse($response, 'success', date('H:i:s T').': '.count($leagues), 200);
    }
}
