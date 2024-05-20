<?php

declare(strict_types=1);

namespace App\Controller\Match;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminMonth extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $month_diff = (int)$request->getQueryParams()['month_diff'] ?? 0;

        $matches = $this->getMatchFindService()->adminGetForMonth($month_diff);

        return $this->jsonResponse($response, 'success', $matches, 200);
    }
}
