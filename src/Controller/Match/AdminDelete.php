<?php

declare(strict_types=1);

namespace App\Controller\Match;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminDelete extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $matchId = (int) $args['id'];
        $deleted = $this->getDeleteMatchService()->delete($matchId);

        return $this->jsonResponse($response, 'success', $deleted, 202);
    }
}
