<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface;

use Slim\Route;

final class Maintenance {

    public function __construct(){}

    public function __invoke(
        Request $request, 
        Response $response, 
        callable $next
    ): ResponseInterface {
        
        // Skip the version check for OPTIONS requests (preflight)
        if ($request->getMethod() === 'OPTIONS') {
            return $next($request, $response);
        }

        $maintenanceMode = isset($_SERVER['MAINTENANCE_MODE']) ? (bool)$_SERVER['MAINTENANCE_MODE'] : null;

        if ($maintenanceMode === true) {
            throw new \App\Exception\Auth('Maintenance.', 503);
        }

        // If the version is valid, proceed to the next middleware/route
        return $next($request, $response);
    }
}
