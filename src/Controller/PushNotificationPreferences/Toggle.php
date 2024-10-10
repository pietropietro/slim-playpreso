<?php

declare(strict_types=1);

namespace App\Controller\PushNotificationPreferences;

use Slim\Http\Request;
use Slim\Http\Response;

final class Toggle extends Base
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

        if (!isset($data->eventType)) {
            throw new \App\Exception\User('missing required fields', 400);
        }

        $eventType = $data->eventType;
        $allowedEvents = $this->getUserNotificationCreateService()->getAllowedEvents();

        if(!in_array($eventType, $allowedEvents)){
            throw new \App\Exception\User('nope!', 400);
        }

        $userId = $this->getAndValidateUserId($request);
        
        if($this->getPushNotificationPreferencesRepository()->hasRejected($userId, $eventType)){
            $res = $this->getPushNotificationPreferencesRepository()->delete($userId, $eventType);
        }else{
            $res = $this->getPushNotificationPreferencesRepository()->create($userId, $eventType);
        }
                 
        return $this->jsonResponse($response, $res ? "success" : "error", $res, 200);
    }
}
