<?php

declare(strict_types=1);

namespace App\Controller\PushNotificationPreferences;

use Slim\Http\Request;
use Slim\Http\Response;

final class Get extends Base
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

        $userId = $this->getAndValidateUserId($request);
        
        $rejected = $this->getPushNotificationPreferencesRepository()->get($userId);

        return $this->jsonResponse($response, "success", $rejected, 200);
    }
}
