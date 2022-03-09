<?php

declare(strict_types=1);

use App\Controller\Guess;
// use App\Controller\Match;
use App\Controller\User;
use App\Middleware\Auth;

return function ($app) {
    $app->group('/api/v1', function () use ($app): void {

        $app->add(function ($req, $res, $next) {
            $response = $next($req, $res);
            return $response
                    ->withHeader('Access-Control-Allow-Origin', $_SERVER['ALLOW_URL_REQUEST'])
                    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        });

        $app->get('', 'App\Controller\DefaultController:getHelp');
        $app->get('/status', 'App\Controller\DefaultController:getStatus');
        $app->post('/login', \App\Controller\User\Login::class);

        $app->group('/users', function () use ($app): void {
            $app->post('', User\Create::class);
            $app->get('/{id}', User\GetOne::class)->add(new Auth());
            $app->put('/{id}', User\Update::class)->add(new Auth());
            // $app->delete('/{id}', User\Delete::class)->add(new Auth());
        });
        // });  
        
        // Catch-all route to serve a 404 Not Found page if none of the routes match
        // NOTE: make sure this route is defined last
        $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
            $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
            return $handler($req, $res);
        });
    });
        
    return $app;
};
