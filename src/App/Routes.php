<?php

declare(strict_types=1);

use App\Controller\User;
use App\Controller\UserParticipation;
use App\Controller\PPLeague;
use App\Controller\Guess;
use App\Controller\Match;
use App\Controller\League;
use App\Controller\PPTournamentType;
use App\Controller\PPCup;
use App\Controller\PPCupGroup;
use App\Controller\PPRound;
use App\Controller\Cron;
use App\Middleware\Auth;
use App\Middleware\Cors;
use App\Middleware\InternalRequest;


return function ($app){
    
    $app->add(function($req, $res, $next) {
        $before = time();
        $res = (new Cors())($req, $res); // before
        $res = $next($req, $res); // route handler
        $res = $res->withHeader('X-Before', $before)->withHeader('X-After', time());
        return $res;
    });

    $app->get('/', 'App\Controller\DefaultController:getHelp');

    $container = $app->getContainer();
    $pointsService = $container->get('points_find_service');
    $admin = $container->get('user_find_service');

    $app->group('/user', function () use ($app, $pointsService): void {
        $app->post('/signup', User\Create::class);
        $app->post('/login', User\Login::class);
        $app->get('/{username}', User\GetOne::class)->add(new Auth($pointsService));
        $app->post('/recover/{username}', User\Recover::class);
        $app->post('/validate-token/{token}', User\ValidateToken::class);
        $app->post('/reset', User\Reset::class);
    });

    $app->post('/guess/lock/{id}', Guess\Lock::class)->add(new Auth($pointsService));
    
    $app->group('/p-tournament-type', function () use ($app): void {
        $app->post('/join/{id}', PPTournamentType\Join::class);
    })->add(new Auth($pointsService));

    $app->group('/p-league', function () use ($app): void {
        $app->get('/available', PPTournamentType\GetAvailablePPLeagues::class);
        $app->get('/{id}', PPLeague\GetOne::class);
    })->add(new Auth($pointsService));

    $app->group('/p-cup', function () use ($app): void {
        $app->get('/available', PPTournamentType\GetAvailablePPCups::class);
        $app->get('/{id}', PPCup\GetOne::class);
        $app->put('/{id}', PPCup\Update::class);
    })->add(new Auth($pointsService));

    $app->group('/user-participation', function () use ($app): void {
        $app->get('/p-leagues', UserParticipation\PPLeagues::class);
        $app->get('/p-cups', UserParticipation\PPCups::class);
    })->add(new Auth($pointsService));

    $app->get('/p-cup-group/{id}', PPCupGroup\GetOne::class)->add(new Auth($pointsService));

    $app->get('/p-round/{id}', PPRound\GetOne::class)->add(new Auth($pointsService));

    $app->group('/admin', function () use ($app): void {
        
        $app->post('/p-cup/{id}', PPCup\Create::class);
        $app->get('/p-cup', PPCup\GetAll::class);
        
        $app->get('/p-tournament-types', PPTournamentType\GetAll::class);
        
        $app->group('/match', function() use($app): void {
            $app->get('', Match\GetAll::class);
            $app->get('/pick/{id}', Match\AdminPick::class);
            $app->post('/{id}', Match\Verify::class);
        });

        $app->group('/league', function() use($app): void {
            $app->get('', League\GetAll::class);
            $app->post('', League\Create::class);
            $app->get('/{id}', League\GetOne::class);
            $app->post('/{id}', League\Update::class);
            $app->post('/fetch/{id}', League\Fetch::class);
        });
        
    })->add(new Auth($pointsService, $admin));

    $app->get('/externalAPI/call', Cron\Start::class)->add(new InternalRequest());

    // Catch-all route to serve a 404 Not Found page if none of the routes match
    // NOTE: make sure this route is defined last
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
        $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
        return $handler($req, $res);
    });

    return $app;
};
