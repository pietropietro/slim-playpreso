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

        $year = (int)($request->getQueryParams()['year'] ?? date('Y'));
        $month = (int)($request->getQueryParams()['month'] ?? date('m'));
        $matchSummary = $this->getMatchExtractSummaryService()->adminGetForMonth($year, $month);

        return $this->jsonResponse($response, 'success', $matchSummary, 200);
    }
}
