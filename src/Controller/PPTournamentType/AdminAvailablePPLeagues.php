<?php

declare(strict_types=1);

namespace App\Controller\PPTournamentType;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminAvailablePPLeagues extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        
        $userId = (int) $args['userId'];
        $ppTournamentTypes = $this->getPPTournamentTypeService()->getAvailablePPLeaguesForUser($userId, ids_only: true);

        return $this->jsonResponse($response, 'success', $ppTournamentTypes, 200);
    }
}
