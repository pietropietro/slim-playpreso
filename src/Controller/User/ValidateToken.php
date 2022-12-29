<?php

declare(strict_types=1);

namespace App\Controller\User;

use Slim\Http\Request;
use Slim\Http\Response;
use App\Service\StoPasswordReset;

final class ValidateToken extends Base
{
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {    

        $token=$args['token'];
        if(!$token){
            throw new \App\Exception\User('missing required field.', 400);
        }    

        $userRecover = $this->getUserRecoverService()->validateToken($token);
        
        return $this->jsonResponse($response, 'success', $userRecover, 200);
    }
}
