<?php

declare(strict_types=1);

namespace App\Controller\ExternalAPI;

use Slim\Http\Request;
use Slim\Http\Response;

final class Update extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
    ): Response {

        $leagues = $this->getLeaguesService()->getNeedData();
        foreach ($leagues as $key => $league) {
            $this->getExternalApiService()->fetchExternalData($league['ls_suffix'], $league['id']);
        }

        return $this->jsonResponse($response, 'success', date('H:i:s').': '.count($leagues), 200);
    }
}
