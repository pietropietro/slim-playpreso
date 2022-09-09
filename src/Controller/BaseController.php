<?php

declare(strict_types=1);

namespace App\Controller;

use Slim\Container;
use Slim\Http\Response;
use Slim\Http\Request;

abstract class BaseController
{
    public function __construct(protected Container $container)
    {
    }

    /**
     * @param array|object|null $message
     */
    protected function jsonResponse(
        Response $response,
        string $status,
        $message,
        int $code
    ): Response {
        $result = [
            'code' => $code,
            'status' => $status,
            'message' => $message,
        ];

        return $response->withJson($result, $code, JSON_PRETTY_PRINT);
    }

    protected static function isRedisEnabled(): bool
    {
        return filter_var($_SERVER['REDIS_ENABLED'], FILTER_VALIDATE_BOOLEAN);
    }

    protected function getAndValidateUserId(Request $request): int    
    {
        $input = (array) $request->getParsedBody();
        if (isset($input['JWT_decoded']) && isset($input['JWT_decoded']->id)) {
            return (int) $input['JWT_decoded']->id;
        }

        throw new User('Invalid user. Permission failed.', 400);
    }
}
