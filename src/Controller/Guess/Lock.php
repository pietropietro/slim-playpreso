<?php

declare(strict_types=1);

namespace App\Controller\Guess;

use Slim\Http\Request;
use Slim\Http\Response;

final class Lock extends Base
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

        if (!isset($data->home) || !isset($data->away)) {
            throw new User('missing required fields', 400);
        }

        $guessId = (int) $args['id'];
        $userId = $this->getAndValidateUserId($request);

        $this->getLockService()->lock($guessId, $userId, $data->home, $data->away);
                 
        return $this->jsonResponse($response, "success", $guessId, 200);
    }
}
