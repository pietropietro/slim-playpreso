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

        //serie a
        $league = $this->getLeaguesService()->getOne(6);
        $created_count = $this->getExternalApiService()->fetchExternalLeagueData($league['ls_suffix'], $league['id']);
        
        return $this->jsonResponse($response, 'success', $created_count, 200);
    }
}
