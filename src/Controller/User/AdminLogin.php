<?php

declare(strict_types=1);

namespace App\Controller\User;

use Slim\Http\Request;
use Slim\Http\Response;
use App\Middleware\Auth;

final class AdminLogin extends Base
{
    public function __invoke(Request $request, Response $response): Response
    {
        $input = (array) $request->getParsedBody();
        
        $user = $this->getLoginUserService()->adminLogin($input);
        if(!$user){
            throw new \Exception("UNAUTHORIZED",403);
        }
        $jwtHeader = Auth::createToken($user['username'], $user['id'], $user['points'], (bool) $user['admin'], $user['created_at']);

        return $this->jsonResponse($response->withHeader('Authorization', $jwtHeader), 'success', null, 200);
    }
}
