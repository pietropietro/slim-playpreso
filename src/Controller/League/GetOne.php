<?php

declare(strict_types=1);

namespace App\Controller\League;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetOne extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $id = (int) $args['id'];
        $league = $this->getFindLeagueService()->getOne(id: $id, admin: true);
        return $this->jsonResponse($response, 'success', $league, 200);
    }
}
