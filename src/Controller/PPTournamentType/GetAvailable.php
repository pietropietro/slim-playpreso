<?php

declare(strict_types=1);

namespace App\Controller\PPTournamentType;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetAvailable extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $input = (array) $request->getParsedBody();
        $userId = $this->getAndValidateUserId($input);
        
        //TODO integrate cups
        $ppTournamentTypes = $this->getPPTournamentTypeService()->getAvailableForUser($userId, false, false);

        return $this->jsonResponse($response, 'success', $ppTournamentTypes, 200);
    }
}
