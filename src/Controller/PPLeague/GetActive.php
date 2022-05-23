<?php

declare(strict_types=1);

namespace App\Controller\PPLeague;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetActive extends Base{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $input = (array) $request->getParsedBody();
        $userId = $this->getAndValidateUserId($input);

        $ppLeagues = $this->getPPLeagueService()->getAll($userId, true);
        return $this->jsonResponse($response, 'success', $ppLeagues, 200);
    }
}