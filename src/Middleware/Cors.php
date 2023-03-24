<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class Cors extends Base
{
    public function __invoke(
        Request $request,
        Response $response,
    ): Response {
        return $response
            ->withHeader('Access-Control-Allow-Origin', $_ENV['ALLOW_URL_REQUEST'])
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Expose-Headers', 'Authorization');
            
    }
}