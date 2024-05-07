<?php

declare(strict_types=1);

namespace App\Controller\League;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminGetAll extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $country = $country = (string)($request->getQueryParams()['country'] ?? null);
        $page = (int) $request->getQueryParam('page', 1); // Default to page 1
        $limit = (int) $request->getQueryParam('limit', null); // Default limit to 50
        $parentOnly = (bool) $request->getQueryParam('parentOnly', false); // Default limit to 50

        $result = $this->getLeagueFindService()->adminGetAll($country, $page, $limit, $parentOnly);

                 
        return $this->jsonResponse($response, 'success', $result, 200);
    }
}
