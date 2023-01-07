<?php

declare(strict_types=1);

namespace App\Controller\PPRoundMatch;

use Slim\Http\Request;
use Slim\Http\Response;

final class Swap extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $ppRoundMatchId = (int) $args['id'];

        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        if (!isset($data->newMatchId)) {
            throw new User('missing required fields', 400);
        }

        $result = $this->getPPRoundMatchUpdateService()->swap($ppRoundMatchId, (int)$data->newMatchId);
                 
        return $this->jsonResponse($response, "success", $result, 200);
    }
}
