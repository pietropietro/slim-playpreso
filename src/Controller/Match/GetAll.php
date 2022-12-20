<?php

declare(strict_types=1);

namespace App\Controller\Match;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetAll extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $days_diff = (int)$request->getQueryParams()['days_diff'] ?? 0;
        $matches = $this->getMatchFindService()->adminGetForWeek($days_diff);
                 
        return $this->jsonResponse($response, 'success', $matches, 200);
    }
}
