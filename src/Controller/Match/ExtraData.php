<?php

declare(strict_types=1);

namespace App\Controller\Match;

use Slim\Http\Request;
use Slim\Http\Response;

final class ExtraData extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $userId = $this->getAndValidateUserId($request);
        $matchId = (int) $args['id'];

        $match = $this->getMatchFindService()->getOne($matchId);


        $leagueStandings = $match['league']['standings'];
        $this->getTeamFindService()->addNameToStandings($leagueStandings);

        $lastFiveHome = $this->getMatchFindService()->getLastForTeam($match['home_id'], 5);
        $lastFiveAway = $this->getMatchFindService()->getLastForTeam($match['away_id'], 5);
        $lastFive = array(
            'home' => $lastFiveHome,
            'away' => $lastFiveAway,
        );

        $extraData = array(
            'leagueStandings' => $leagueStandings,
            'lastMatches' => $lastFive,
        );
                 
        return $this->jsonResponse($response, "success", $extraData, 200);
    }
}
