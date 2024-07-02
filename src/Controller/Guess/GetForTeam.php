<?php

declare(strict_types=1);

namespace App\Controller\Guess;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetForTeam extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $userId = $this->getAndValidateUserId($request);
        $teamId = (int) $args['id'];

        $from = $request->getQueryParams()['from'] ?? null;
        $to = $request->getQueryParams()['to'] ?? null;

        $guesses = $this->getGuessFindService()->getForTeam(
            $teamId, $userId, $from, $to
        );
                 
        return $this->jsonResponse($response, "success", $guesses, 200);
    }
}
