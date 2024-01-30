<?php

declare(strict_types=1);

namespace App\Controller\UserNotification;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetAll extends Base{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $userId = $this->getAndValidateUserId($request);

        $notifications = $this->getUserNotificationFindService()->getForUser($userId);
    
        return $this->jsonResponse($response, 'success', $notifications, 200);
    }
}