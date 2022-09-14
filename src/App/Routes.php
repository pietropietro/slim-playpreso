<?php

declare(strict_types=1);

use App\Controller\User;
use App\Controller\UserParticipation;
use App\Controller\PPLeague;
use App\Controller\Guess;
use App\Controller\PPTournamentType;
use App\Controller\PPCup;
use App\Controller\PPCupGroup;
use App\Controller\ExternalAPI;
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
    $pointsService = $container->get('points_find_service');

    $app->group('/user', function () use ($app, $pointsService): void {
        $app->post('', User\Create::class);
        $app->get('/{username}', User\GetOne::class)->add(new Auth($pointsService));
    });

    $app->post('/guess/lock/{id}', Guess\Lock::class)->add(new Auth($pointsService));
    
    $app->group('/ppTournamentType', function () use ($app): void {
        $app->get('/available', PPTournamentType\GetAvailable::class);
        $app->post('/join/{id}', PPTournamentType\Join::class);
    })->add(new Auth($pointsService));

    $app->get('/ppLeague/{id}', PPLeague\GetOne::class)->add(new Auth($pointsService));
    
    $app->get('/userParticipation/ppLeagues', UserParticipation\PPLeagues::class)->add(new Auth($pointsService));
    
    $app->group('/ppCup', function () use ($app): void {
        $app->get('/{id}', PPCup\GetOne::class);
        $app->put('/{id}', PPCup\Update::class);
    })->add(new Auth($pointsService));

    $app->get('/ppCupGroup/{id}', PPCupGroup\GetOne::class)->add(new Auth($pointsService));

    $app->get('/externalAPI/call', ExternalAPI\Update::class);

    // Catch-all route to serve a 404 Not Found page if none of the routes match
    // NOTE: make sure this route is defined last
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
        $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
        return $handler($req, $res);
    });

    return $app;
};
