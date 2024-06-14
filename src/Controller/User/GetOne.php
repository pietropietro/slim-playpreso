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

        $user = $this->getFindUserService()->getOneFromUsername(
            username:(string) $args['username'],
            sensitiveColumns: false
        );

        if(!$user){
            throw new \App\Exception\User('User not found.', 404);
        }
        
        $page = (int) $request->getQueryParam('page', 1); // Default to page 1
        $limit = (int) $request->getQueryParam('limit', 200); // Default limit 


        $user['ppDex'] = $this->getPPDexFindService()->getUserPPDex($user['id']);
        $user['trophies'] = $this->getTrophiesFindService()->getTrophies($user['id'], null);  
        $user['verified_guesses'] = $this->getGuessFindService()->getForUser(
            userId: $user['id'],
            includeMotd: true,
            locked: null,
            verified: true,
            order: 'desc',
            page: 1,
            limit:20
        );

        return $this->jsonResponse($response, 'success', $user, 200);
    }
}
