<?php

declare(strict_types=1);

namespace App\Controller\PPArea;

use Slim\Http\Request;
use Slim\Http\Response;

final class RemoveLeague extends Base
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

        $ppAreaId = (int) $args['id'];
        $leagueId = (int) $args['leagueId'];
        $result = $this->getPPAreaUpdateService()->removeLeague($ppAreaId, $leagueId);
                 
        return $this->jsonResponse($response, "success", $result, 200);
    }
}
