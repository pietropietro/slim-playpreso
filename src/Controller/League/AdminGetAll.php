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

        $leagues = $this->getLeagueFindService()->adminGetAll($country);

                 
        return $this->jsonResponse($response, 'success', $leagues, 200);
    }
}
