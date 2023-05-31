<?php

declare(strict_types=1);

namespace App\Controller\PPTournamentType;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetAvailablePPLeagues extends Base
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

        $activePPLeagues = $this->getUserParticipationFindService()->getForUser(
            $userId, 'ppLeague', true, false
        );

        if(count($activePPLeagues) >= $_SERVER['MAX_CONCURRENT_PPLEAGUES']){
            return $this->jsonResponse($response, 'limit_reached', null, 200);
        }

        $availablePPTournamentTypes = $this->getPPTournamentTypeFindService()->getAvailablePPLeaguesForUser($userId, ids_only: false);

        //FILTER OUT PPTT WITH NOT ENOUGH MATCHES IN NEAR FUTURE
        $filteredPPTTs = $this->getPPTournamentTypeFindService()->filterByMatchAvailability(array_column($availablePPTournamentTypes, 'id'));

        //get ppTTs whose p-leagues have most players. returns null otherwise.
        $ppTTtoStart = $this->getPPTournamentTypeFindService()->getCloseToStart(array_column($filteredPPTTs, 'id'));

        $returnArray = !empty($ppTTtoStart) ? $ppTTtoStart : $filteredPPTTs;
        return $this->jsonResponse($response, 'success', $returnArray, 200);
    }
}
