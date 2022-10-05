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
        $ppTournamentTypes = $this->getPPTournamentTypeService()->getAvailablePPLeaguesForUser($userId, only_ids: false);

        return $this->jsonResponse($response, 'success', $ppTournamentTypes, 200);
    }
}
