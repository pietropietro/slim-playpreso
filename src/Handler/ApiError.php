<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Middleware\Cors;

final class ApiError extends \Slim\Handlers\Error
{
    public function __invoke(
        Request $request,
        Response $response,
        \Exception $exception
    ): Response {
        $statusCode = $this->getStatusCode($exception);
        $className = new \ReflectionClass($exception::class);

        $response = (new Cors())($request, $response);

        $errorMessage = [
            'message' => 'Something went wrong',
        ];
        if ($_SERVER['DEBUG'] === 'true') {
            $errorMessage['trace'] = $exception->getTraceAsString();
            $errorMessage['message'] = $exception->getMessage();
        }

        $data = [
            'message' => $errorMessage,
            'class' => $className->getName(),
            'status' => 'error',
            'code' => $statusCode,
        ];
        $body = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $response->getBody()->write((string) $body);

        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-type', 'application/problem+json');
    }

    public static function getStatusCode(\Exception $exception): int
    {
        $statusCode = 500;
        if (is_int($exception->getCode()) &&
            $exception->getCode() >= 400 &&
            $exception->getCode() <= 500
        ) {
            $statusCode = $exception->getCode();
        }

        return $statusCode;
    }
}
