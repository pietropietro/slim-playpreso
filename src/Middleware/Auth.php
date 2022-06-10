<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;
use Firebase\JWT\JWT;

final class Auth extends Base
{
    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {
        $jwtHeader = $request->getHeaderLine('Authorization');
        if (! $jwtHeader) {
            throw new \App\Exception\Auth('JWT Token required.', 400);
        }
        $jwt = explode('Bearer ', $jwtHeader);
        if (! isset($jwt[1])) {
            throw new \App\Exception\Auth('JWT Token invalid.', 400);
        }
        $decoded = $this->checkToken($jwt[1]);

        $requestBody = (array) $request->getParsedBody();
        $requestBody['JWT_decoded'] = $decoded;

        $updatedJWT = Auth::createToken($requestBody['JWT_decoded']->username, $requestBody['JWT_decoded']->id);

        return $next($request->withParsedBody($requestBody), $response->withHeader('Authorization', $updatedJWT));
    }

    public static function createToken($username, $userId) : string {
        $token = [
            'username' => $username,
            'id' => $userId,
            'iat' => time(),
            'exp' => time() + ($_SERVER['TOKEN_VALIDITY_DAYS'] * 24 * 60 * 60),
        ];

        return 'Bearer ' . JWT::encode($token, $_SERVER['SECRET_KEY']);
    }

    protected function checkToken(string $token): object
    {
        try {
            return JWT::decode($token, $_SERVER['SECRET_KEY'], ['HS256']);
        } catch (\UnexpectedValueException) {
            throw new Auth('Forbidden: you are not authorized.', 403);
        }
    }
}
