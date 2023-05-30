<?php

declare(strict_types=1);

namespace App\Controller\PPArea;

use Slim\Http\Request;
use Slim\Http\Response;

final class AddLeague extends Base
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

        if (!isset($data->leagueId)) {
            throw new \App\Exception\User('missing required fields', 400);
        }

        $ppAreaId = (int) $args['id'];
        $result = $this->getPPAreaUpdateService()->addLeague($ppAreaId, $data->leagueId);
                 
        return $this->jsonResponse($response, "success", $result, 200);
    }
}
