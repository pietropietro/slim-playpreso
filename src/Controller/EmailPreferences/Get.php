<?php

declare(strict_types=1);

namespace App\Controller\EmailPreferences;

use Slim\Http\Request;
use Slim\Http\Response;

final class Get extends Base{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $userId = $this->getAndValidateUserId($request);

        $preferences = $this->getEmailPreferencesFindService()->getForUser($userId);

        return $this->jsonResponse($response, 'success', $preferences, 200);
    }
}