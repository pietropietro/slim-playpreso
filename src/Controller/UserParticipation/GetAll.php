<?php

declare(strict_types=1);

namespace App\Controller\UserParticipation;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetAll extends Base{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $userId = $this->getAndValidateUserId($request);

        //TODO change type to enum
        $ups = $this->getParticipationService()->getForUser($userId, null, started: null, finished: false);

        return $this->jsonResponse($response, 'success', $ups, 200);
    }
}