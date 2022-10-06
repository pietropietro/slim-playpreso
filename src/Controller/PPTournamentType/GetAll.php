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
        
        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        $onlyCups = $request->getQueryParams()['onlyCups'] ?? null;
        $ppTournamentTypes = $this->getPPTournamentTypeService()->get(null, onlyCups: !!$onlyCups);

        return $this->jsonResponse($response, 'success', $ppTournamentTypes, 200);
    }
}
