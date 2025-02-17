<?php

declare(strict_types=1);

namespace App\Controller\UserNotification;

use Slim\Http\Request;
use Slim\Http\Response;

final class Read extends Base{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $userId = $this->getAndValidateUserId($request);

        $this->getUserNotificationReadService()->setRead($userId);
       
        // if($enriched)$this->getUserNotificationReadService()->setRead($userId);

        return $this->jsonResponse($response, 'success', null, 200);
    }
}