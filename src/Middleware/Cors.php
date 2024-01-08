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

        //admin.playpreso.com try
        //it was working but had nginx issues
        // $allowedOrigins = explode(',', $_ENV['ALLOW_URL_REQUEST']);
        // $origin = $request->getHeaderLine('Origin');
    
        // if (in_array($origin, $allowedOrigins)) {
        //     return $response
        //         ->withHeader('Access-Control-Allow-Origin', $origin)
        //         ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        //         ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        //         ->withHeader('Access-Control-Allow-Credentials', 'true')
        //         ->withHeader('Access-Control-Expose-Headers', 'Authorization');
        // } else {
        //     return $response->withStatus(403, 'Forbidden');
        // }


        return $response
            ->withHeader('Access-Control-Allow-Origin', $this->getAllowedOrigin($request))
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Expose-Headers', 'Authorization');
            
    }

    private function getAllowedOrigin(Request $request): string {
        $allowedOrigins = ['https://playpreso.com', 'capacitor://localhost', 'http://localhost:3000'];
        $origin = $request->getHeaderLine('Origin');
    
        if (in_array($origin, $allowedOrigins)) {
            return $origin;
        }
    
        return 'https://playpreso.com'; // Default origin if not matched
    }
}