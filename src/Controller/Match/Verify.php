<?php

declare(strict_types=1);

namespace App\Controller\Match;

use Slim\Http\Request;
use Slim\Http\Response;

final class Verify extends Base
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

        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        if (!isset($data->home) || !isset($data->away)) {
            throw new User('missing required fields', 400);
        }

        $this->getVerifyMatchService()->verify($id, $data->home, $data->away);
        return $this->jsonResponse($response, 'success', date("Y-m-d H:i:s"), 200);
    }
}
