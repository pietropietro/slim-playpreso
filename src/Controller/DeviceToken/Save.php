<?php

declare(strict_types=1);

namespace App\Controller\DeviceToken;

use Slim\Http\Request;
use Slim\Http\Response;

final class Save extends Base
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

        if (!isset($data->token) || !isset($data->platform)) {
            throw new \App\Exception\User('missing required fields', 400);
        }

        $userId = $this->getAndValidateUserId($request);
        $deviceToken = $data->token;
        $platform = $data->platform;

        $result = $this->getDeviceTokenRepository()->save($userId, $deviceToken, $platform);
                 
        return $this->jsonResponse($response, "success", $result, 200);
    }
}
