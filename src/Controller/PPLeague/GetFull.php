<?php

declare(strict_types=1);

namespace App\Controller\PPLeague;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetFull extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $ppLeague = $this->getPPLeagueService()->getFull((int) $args['id']);

        return $this->jsonResponse($response, 'success', $ppLeague, 200);
    }
}
