<?php

declare(strict_types=1);

namespace App\Controller\PPArea;

use Slim\Http\Request;
use Slim\Http\Response;

final class RemoveTournament extends Base
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
        $tournamentId = (int) $args['tournamentId'];
        $result = $this->getPPAreaUpdateService()->removeTournament($ppAreaId, $tournamentId);
                 
        return $this->jsonResponse($response, "success", $result, 200);
    }
}
