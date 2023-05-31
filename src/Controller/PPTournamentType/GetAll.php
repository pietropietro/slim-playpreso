<?php

declare(strict_types=1);

namespace App\Controller\PPTournamentType;

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
        
        $onlyCups = $request->getQueryParams()['onlyCups'] ?? null;
        $ppTournamentTypes = $this->getPPTournamentTypeFindService()->get(null, onlyCups: !!$onlyCups, enriched: true);

        return $this->jsonResponse($response, 'success', $ppTournamentTypes, 200);
    }
}
