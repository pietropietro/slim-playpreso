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
        if(!$token || !StoPasswordReset::isTokenValid($token)){
            throw new \App\Exception\User('Invalid token.', 400);
        }        

        $hash = StoPasswordReset::calculateTokenHash($token);
        $user = $this->getUserRecoverService()->getUserFromToken($hash);
        
        return $this->jsonResponse($response, 'success', $user, 200);
    }
}
