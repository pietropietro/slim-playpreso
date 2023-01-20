<?php

declare(strict_types=1);

namespace App\Controller\User;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminGetAll extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        if(!$users = $this->getFindUserService()->adminGet()){
            throw new \App\Exception\User('no users', 404);
        }

        return $this->jsonResponse($response, 'success', $users, 200);
    }
}
