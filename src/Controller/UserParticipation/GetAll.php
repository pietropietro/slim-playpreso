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

        $ups = array(
            "active" => $this->getParticipationService()->getForUser($userId, null, started: true, finished: false),
            "notStarted" => $this->getParticipationService()->getForUser($userId, null, started: false, finished: false),
            "finished" => $this->getParticipationService()->getForUser(
                $userId, 
                'ppLeague', 
                started: null, 
                finished: true, 
                updatedAfter: '-1 month'
            ),
        );

        return $this->jsonResponse($response, 'success', $ups, 200);
    }
}