<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;
use Firebase\JWT\JWT;
use \App\Service\Points;
use \App\Service\User;

final class Auth extends Base
{
    public function __construct(
        protected Points\Find $pointsService,
        protected ?User\Find $findUserService = null
    ){}

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
            throw new \App\Exception\Auth('JWT Token invalid.', 401);
        }
        $decoded = Auth::checkToken($jwt[1]);

        $requestBody = (array) $request->getParsedBody();
        $requestBody['JWT_decoded'] = $decoded;

        $user_id = $requestBody['JWT_decoded']->id;
        $points = $this->pointsService->get($user_id);

        if(!!$this->findUserService && !$this->findUserService->isAdmin($user_id)){
            throw new \App\Exception\Auth('FORBIDDEN – NOT AN ADMIN.', 403);
        }
        
        $updatedJWT = Auth::createToken(
            $requestBody['JWT_decoded']->username, 
            $user_id,
            $points,
            $requestBody['JWT_decoded']->admin,
            $requestBody['JWT_decoded']->created_at
        );

        return $next($request->withParsedBody($requestBody), $response->withHeader('Authorization', $updatedJWT));
    }

    public static function createToken(string $username, int $userId, int $points, bool $admin, string $created_at) : string {
        $token = [
            'username' => $username,
            'id' => $userId,
            'points' => $points,
            'admin' => $admin,
            'created_at' => $created_at,
            'iat' => time(),
            'exp' => time() + ($_SERVER['TOKEN_VALIDITY_DAYS'] * 24 * 60 * 60),
        ];

        return 'Bearer ' . JWT::encode($token, $_SERVER['SECRET_KEY']);
    }

    public static function checkToken(string $token): object
    {
        try {
            return JWT::decode($token, $_SERVER['SECRET_KEY'], ['HS256']);
        } catch (\UnexpectedValueException) {
            throw new \App\Exception\Auth('jwt not valid.', 401);
        }
    }
}
