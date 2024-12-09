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
            ->withHeader('Access-Control-Allow-Origin', $this->getAllowedOrigin($request))
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Origin, Authorization, X-Frontend-Version')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Expose-Headers', 'Authorization');
            
    }

    private function getAllowedOrigin(Request $request): string {
        if($_SERVER['DEBUG']){
            //'capacitor://localhost', 'http://localhost:3000', 'http://0.0.0.0:3000'
            $origin = $request->getHeaderLine('Origin');
            return $origin;
        }
        else{
            $allowedOrigins = [
                'https://playpreso.com', 
                'https://admin.playpreso.com',
                'capacitor://localhost', 
                'https://localhost'
            ];
            $origin = $request->getHeaderLine('Origin');
        
            if (in_array($origin, $allowedOrigins)) {
                return $origin;
            }
        }
    
        return 'https://playpreso.com'; // Default origin if not matched
    }
}