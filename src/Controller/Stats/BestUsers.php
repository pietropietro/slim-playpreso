<?php

declare(strict_types=1);

namespace App\Controller\Stats;

use Slim\Http\Request;
use Slim\Http\Response;

final class BestUsers extends Base
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

        $bestUsers = $this->getFindStatsService()->bestUsers($userId);
        
        return $this->jsonResponse($response, 'success', $bestUsers, 200);
    }
}
