<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface;

use Slim\Route;

final class VersionCheck {

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

        // Skip version check for team image route. //TODO delete when FE will pass version
        $uriPath = $request->getUri()->getPath();
        if (preg_match('#^/static/teams/[^/]+$#', $uriPath)) {
            return $next($request, $response);
        }

        // Skip version check if the request is coming from the admin frontend
        $adminOrigin = 'https://admin.playpreso.com'; // Adjust if necessary
        $originHeader = $request->getHeaderLine('Origin');
        $refererHeader = $request->getHeaderLine('Referer');

        if ($originHeader === $adminOrigin || strpos($refererHeader, $adminOrigin) === 0 || $_SERVER['DEBUG'] === 'true') {
            return $next($request, $response);
        }


        // Get the X-Frontend-Version header
        $frontendVersion = $request->getHeaderLine('X-Frontend-Version');
        $minFrontendVersion = isset($_SERVER['MINIMUM_FE_VERSION']) ? $_SERVER['MINIMUM_FE_VERSION'] : null;

        // Check if the app version is valid and higher than the minimum required version
        if ($minFrontendVersion && version_compare($frontendVersion, $minFrontendVersion, '<')) {
            //throw exception instead of returning a reponse because
            //exception has the required headers added to the resp back.
            //otherwise would get CORS error
            throw new \App\Exception\Auth('Update app required', 426);
        }

        // If the version is valid, proceed to the next middleware/route
        return $next($request, $response);
    }
}
