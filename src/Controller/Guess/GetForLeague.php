<?php

declare(strict_types=1);

namespace App\Controller\Guess;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetForLeague extends Base
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
        $leagueId = (int) $args['id'];

        $before = $request->getQueryParams()['before'] ?? null;
        $after = $request->getQueryParams()['after'] ?? null;

        $guesses = $this->getGuessFindService()->getForLeague(
            $leagueId, $userId, $before, $after
        );
                 
        return $this->jsonResponse($response, "success", $guesses, 200);
    }
}
