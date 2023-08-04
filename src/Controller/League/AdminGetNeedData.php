<?php

declare(strict_types=1);

namespace App\Controller\League;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminGetNeedData extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $withGuesses = (bool) isset($request->getQueryParams()['withGuesses']) ?? false;
        $leagues = $this->getLeagueFindService()->getNeedData($withGuesses);
        return $this->jsonResponse($response, 'success', $leagues, 200);
    }
}
