<?php

declare(strict_types=1);

namespace App\Controller\League;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminGetLeagueCountries extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $countries = $this->getLeagueFindService()->adminGetCountries();
                 
        return $this->jsonResponse($response, 'success', $countries, 200);
    }
}
