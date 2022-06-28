<?php

declare(strict_types=1);

namespace App\Controller\User;

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
        $id = $this->getFindUserService()->idFromUsername((string) $args['username']);
        if(!$id){
            throw new \App\Exception\User('User not found.', 404);
        }
        $user = $this->getFindUserService()->getOne($id);
        $user['trophies'] = $this->getParticipationService()->getTrophies($id);

        return $this->jsonResponse($response, 'success', $user, 200);
    }
}
