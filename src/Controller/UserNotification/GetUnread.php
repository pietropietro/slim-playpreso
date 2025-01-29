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

        $enriched = isset($request->getQueryParams()['enriched']) 
        ? (bool) $request->getQueryParams()['enriched'] 
         : false;

        $notifications = $this->getUserNotificationFindService()->getUnread($userId, $enriched);


        // WRAPPED HARD-INSERT NOTIFICATION
        // Check if today is between December 24 and January 6
        $currentDate = new \DateTime();
        $start = new \DateTime($currentDate->format('Y') . '-12-11');
        $end = new \DateTime('2025-01-07');

        if ($currentDate >= $start && $currentDate < $end) {
            // Add the dummy "year_wrapped" notification to the list
            $notifications[] = [
                'id' => null, // Dummy notification, no real ID
                'user_id' => $userId,
                'event_type' => 'year_wrapped',
                'event_id' => null,
                'created_at' => $currentDate->format('Y-m-d H:i:s'),
                'updated_at' => $currentDate->format('Y-m-d H:i:s'),
            ];
        }


        if($enriched)$this->getUserNotificationReadService()->setRead($userId);

        return $this->jsonResponse($response, 'success', $notifications, 200);
    }
}