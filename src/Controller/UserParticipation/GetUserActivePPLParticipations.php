<?php

declare(strict_types=1);

namespace App\Controller\UserParticipation;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetUserActivePPLParticipations extends Base{
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

        //TODO change type to enum
        $ups = $this->getUserParticipationService()->getAll($userId, 'ppLeague', true);
        return $this->jsonResponse($response, 'success', $ups, 200);
    }
}