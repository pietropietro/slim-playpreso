<?php

declare(strict_types=1);

namespace App\Controller\User;

use Slim\Http\Request;
use Slim\Http\Response;
use App\Middleware\Auth;

final class Login extends Base
{
    public function __invoke(Request $request, Response $response): Response
    {
        $input = (array) $request->getParsedBody();
        
        $user = $this->getLoginUserService()->login($input);
        $jwtHeader = Auth::createToken($user['username'], $user['id'], $user['points'], (bool) $user['admin'], $user['created_at']);
        
        // return $this->jsonResponse($response->withHeader('Authorization', $jwtHeader), 'success', null, 200);

        //TODO DELETE – TEMPORARY FIX - to delete with next client app release
        $message = ['user' => $user];
        return $this->jsonResponse($response->withHeader('Authorization', $jwtHeader), 'success', $message, 200);

    }
}
