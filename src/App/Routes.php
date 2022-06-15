<?php

declare(strict_types=1);

use App\Controller\User;
use App\Controller\UserParticipation;
use App\Controller\PPLeague;
use App\Controller\PPLeagueType;
use App\Middleware\Auth;
use App\Middleware\Cors;


return function ($app){
    
    $app->add(function($req, $res, $next) {
        $before = time();
        $res = (new Cors())($req, $res); // before
        $res = $next($req, $res); // route handler
        $res = $res->withHeader('X-Before', $before)->withHeader('X-After', time());
        return $res;
    });

    $app->get('/', 'App\Controller\DefaultController:getHelp');
    $app->post('/login', \App\Controller\User\Login::class);

    $container = $app->getContainer();
    $pointService = $container->get('user_points_service');

    $app->group('/user', function () use ($app, $pointService): void {
        $app->post('', User\Create::class);
        $app->get('/{id}', User\GetOne::class)->add(new Auth($pointService));
        //TODO
        // $app->put('/{id}', User\Update::class)->add(new Auth($pointService));
        // $app->delete('/{id}', User\Delete::class)->add(new Auth($pointService));
    });
    
    $app->group('/ppLeagueType', function () use ($app, $pointService): void {
        $app->get('/{id}', PPLeagueType\Find::class)->add(new Auth($pointService));
        $app->get('/ppLeagueType/available', PPLeagueType\GetAvailable::class)->add(new Auth($pointService));
        $app->post('/ppLeagueType/join/{id}', PPLeagueType\Join::class)->add(new Auth($pointService));
    });


    $app->get('/ppLeague/{id}', PPLeague\GetFull::class)->add(new Auth($pointService));
    
    $app->get('/userParticipation/ppLeagues', UserParticipation\PPLeagues::class)->add(new Auth($pointService));

    
    // Catch-all route to serve a 404 Not Found page if none of the routes match
    // NOTE: make sure this route is defined last
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
        $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
        return $handler($req, $res);
    });

    return $app;
};
