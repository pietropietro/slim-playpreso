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
        $availablePPTournamentTypes = $this->getPPTournamentTypeService()->getAvailablePPLeaguesForUser($userId, ids_only: false);

        //get ppTTs whose p-leagues have most players
        $ppTTtoStart = $this->getPPTournamentTypeService()->getCloseToStart(array_column($availablePPTournamentTypes, 'id'));

        $returnArray = !empty($ppTTtoStart) ? $ppTTtoStart : $availablePPTournamentTypes;
        return $this->jsonResponse($response, 'success', $returnArray, 200);
    }

}
