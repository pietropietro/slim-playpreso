<?php

declare(strict_types=1);

namespace App\Controller\UserNotification;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetUnread extends Base{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $userId = $this->getAndValidateUserId($request);

       
        $page = (int) $request->getQueryParam('page', 1); // Default to page 1
        $limit = (int) $request->getQueryParam('limit', 10); // Default limit to 10
        $notifications = $this->getUserNotificationFindService()->getUnread($userId, $page, $limit);
       


        // if($enriched)$this->getUserNotificationReadService()->setRead($userId);

        return $this->jsonResponse($response, 'success', $notifications, 200);
    }
}