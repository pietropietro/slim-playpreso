<?php

declare(strict_types=1);

namespace App\Controller\UserParticipation;

use Slim\Http\Request;
use Slim\Http\Response;

final class PPCups extends Base{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $userId = $this->getAndValidateUserId($request);

        $ups = $this->getParticipationService()->getUserParticipations($userId, 'ppCup', active: true);

        return $this->jsonResponse($response, 'success', $ups, 200);
    }
}