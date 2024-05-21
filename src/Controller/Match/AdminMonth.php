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
        $matchSummary = $this->getMatchFindService()->adminGetSummaryForMonth($month_diff);

        // EXAMPLE OUTPUT
        // {
        //     "match_day": "2024-03-09",
        //     "match_count": 273,
        //     "countries": {
        //         "Germany": [
        //             {
        //                 "name": "3. Liga",
        //                 "id": 12,
        //                 "subLeagues": [
        //                     {"name": "Nord", "id": 18},
        //                     {"name": "Nordost", "id": 321}
        //                 ]
        //             },
        //             {
        //                 "name": "4a Liga",
        //                 "id": 21,
        //                 "subLeagues": []
        //             }
        //         ]
        //     }
        // }

        return $this->jsonResponse($response, 'success', $matchSummary, 200);
    }
}
