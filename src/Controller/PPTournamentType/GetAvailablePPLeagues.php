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

        if(!$this->getCheckPPTournamentService()->isBelowPPLeaguesConcurrentLimit($userId)){
            return $this->jsonResponse($response, 'limit_reached', null, 200);
        }

        $availablePPTournamentTypes = $this->getPPTournamentTypeFindService()->getAvailablePPLeaguesForUser($userId, ids_only: false);

        //FILTER OUT PPTT WITH NOT ENOUGH MATCHES IN NEAR FUTURE
        $filteredPPTTs = $this->getPPTournamentTypeFindService()->filterByMatchAvailability(array_column($availablePPTournamentTypes, 'id'));

        //get ppTTs whose p-leagues have most players. returns null otherwise.
        $withParticipants = $this->getPPTournamentTypeFindService()->getHavingParticipants(array_column($filteredPPTTs, 'id'));

        if(!empty($withParticipants)){
            $ids = array_column($withParticipants, 'id');
            foreach ($filteredPPTTs as $pptt) {
                if(!in_array($pptt['id'], $ids)){
                    array_push($withParticipants, $pptt);
                }
            }
            return $this->jsonResponse($response, 'success', $withParticipants, 200);
        }
        
        return $this->jsonResponse($response, 'success', $filteredPPTTs, 200);
    }
}
