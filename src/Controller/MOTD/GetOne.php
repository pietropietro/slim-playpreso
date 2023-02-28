<?php

declare(strict_types=1);

namespace App\Controller\MOTD;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetOne extends Base
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
        $motd = $this->getPPRoundMatchFindService()->getLastMotd($userId);
        return $this->jsonResponse($response, 'success', $motd, 200);
    }
}
