<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

final class InternalRequest extends Base
{
    public function __construct(){}

    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {

        if(!$_SERVER['DEBUG'] && ($_SERVER['REMOTE_ADDR'] !== $_SERVER['SERVER_ADDR'])){
            throw new \App\Exception\Auth('FORBIDDEN – NOT ALLOWED.', 403);
        }
        
        return $next($request->withParsedBody($request), $response);
    }

}
