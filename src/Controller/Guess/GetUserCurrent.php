<?php

declare(strict_types=1);

namespace App\Controller\Guess;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetUserCurrent extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $userId = $this->getAndValidateUserId($request);
        $unlocked = $this->getFindGuessService()->getUnlockedForUser($userId);
        $locked = $this->getFindGuessService()->getLockedForUser($userId);

        $data = [];
        if (!empty($unlocked)) {
            $data['unlocked'] = $unlocked;
        }
        if (!empty($locked)) {
            $data['locked'] = $locked;
        }

        return $this->jsonResponse(
            $response, 
            "success", 
            $data,    
            200
        );
    }
}
