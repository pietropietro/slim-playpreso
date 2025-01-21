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
        $unlocked = $this->getGuessFindService()->getForUser(
            userId: $userId, 
            includeMotd: false,
            locked: false,
            verified: false
        );

        $locked = $this->getGuessFindService()->getForUser(
            userId: $userId, 
            includeMotd: true,
            locked: true,
            verified: false
        );

        $verified = $this->getGuessFindService()->getForUser(
            userId: $userId, 
            includeMotd: true,
            locked: null,
            verified: true,
            verified_after:  date("Y-m-d", strtotime('- 4 days')),
            order: 'desc'
        );

        $data = [];
        if (!empty($unlocked)) {
            $data['unlocked'] = $unlocked;
        }
        if (!empty($locked)) {
            $data['locked'] = $locked;
        }
        if (!empty($verified)) {
            $data['finished'] = $verified;
        }

        return $this->jsonResponse(
            $response, 
            "success", 
            $data,    
            200
        );
    }
}
