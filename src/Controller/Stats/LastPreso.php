<?php

declare(strict_types=1);

namespace App\Controller\Stats;

use Slim\Http\Request;
use Slim\Http\Response;

final class LastPreso extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        // $userId = $this->getAndValidateUserId($request);

        $lastPreso = $this->getFindStatsService()->lastPreso();
        
        return $this->jsonResponse($response, 'success', $lastPreso, 200);
    }
}
