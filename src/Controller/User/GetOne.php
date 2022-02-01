<?php

declare(strict_types=1);

namespace App\Controller\User;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetOne extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $user = $this->getFindUserService()->getOne((int) $args['id']);


        //maybe here add  other data?
        /*
            //get guesses in last 3 months
            $guesses = getGuessesForUser($user['user_id']);
            //add guesses to the retrieved json
            $user['guesses'] = $guesses;
            //add presoLeagues
            $user['presoLeagues'] = returnUserPresoLeagues($user['user_id']);
            if($guesses){
                //add TopStats
                // $user['userTopStats'] = getTopStatsForUser($user['user_id']);
                $user['leagueTopStats'] = getTopStats($user['user_id']);
                //add Trophies
                $user['trophies'] = getUserTrophies($user['user_id']);
                //average
                $user['average'] = calculateUserAverage($user['user_id'],20);
            }
        */
        
        return $this->jsonResponse($response, 'success', $user, 200);
    }
}
