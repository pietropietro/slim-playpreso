<?php

declare(strict_types=1);

use App\Controller\User;
use App\Controller\UserParticipation;
use App\Controller\PPLeague;
use App\Middleware\Auth;

$cors = function ($req, $res) {
    return $res
            ->withHeader('Access-Control-Allow-Origin', $_SERVER['ALLOW_URL_REQUEST'])
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->withHeader('Access-Control-Allow-Credentials', 'true');
};


return function ($app) use ($cors) {
    $c = $app->getContainer();
    //TODO MOVE IN HANDLER DIR
    $c['phpErrorHandler'] = $c['errorHandler'] = function ($c) use ($cors) {
        return function($request, $response, $exception) use ($cors) {
            $response = $cors($request, $response);

            $message = [
                'message' => 'Something went wrong',
            ];
            if ($_SERVER['DEBUG'] === 'true') {
                $message['trace'] = $exception->getTraceAsString();
                $message['message'] = $exception->getMessage();
            }

            $statusCode = 500;
            if (is_int($exception->getCode()) &&
                $exception->getCode() >= 400 &&
                $exception->getCode() <= 500
            ) {
                $statusCode = $exception->getCode();
            }
            
            return $response->withJson($message, $statusCode);
        };
    };

    $app->add(function($req, $res, $next) use ($cors) {
        $before = time();
        $res = $cors($req, $res); // before
        $res = $next($req, $res); // route handler
        $res = $res->withHeader('X-Before', $before)->withHeader('X-After', time());
        return $res;
    });


    $app->get('/', 'App\Controller\DefaultController:getHelp');
    $app->post('/login', \App\Controller\User\Login::class);

    $app->group('/users', function () use ($app): void {
        $app->post('', User\Create::class);
        $app->get('/{id}', User\GetOne::class)->add(new Auth());
        //TODO
        // $app->put('/{id}', User\Update::class)->add(new Auth());
        // $app->delete('/{id}', User\Delete::class)->add(new Auth());
    });
    
    $app->get('/ppLeagueTypes', User\GetPPLeagueTypes::class)->add(new Auth());
    $app->get('/ppLeague/{id}', PPLeague\GetFull::class)->add(new Auth);
    $app->get('/activePPLeaguesParticipations', UserParticipation\GetUserActivePPLParticipations::class)->add(new Auth());

    
    // Catch-all route to serve a 404 Not Found page if none of the routes match
    // NOTE: make sure this route is defined last
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
        $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
        return $handler($req, $res);
    });

    return $app;
};
