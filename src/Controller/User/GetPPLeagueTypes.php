<?php

declare(strict_types=1);

namespace App\Controller\User;

use Slim\Http\Request;
use Slim\Http\Response;

//TODO MOVE IN PPLeagueType CONTROLLER FOLDER
final class GetPPLeagueTypes extends Base
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

        $ppLeagueTypes = $this->getFindUserService()->getAvailablePPLeagueTypes($userId);

        return $this->jsonResponse($response, 'success', $ppLeagueTypes, 200);
    }
}
