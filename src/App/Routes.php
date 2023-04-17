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
use App\Controller\PPRoundMatch;
use App\Controller\EmailPreferences;
use App\Controller\Cron;
use App\Controller\Stats;
use App\Controller\MOTD;
use App\Controller\StaticFiles;
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

    $container = $app->getContainer();
    $pointsService = $container->get('points_find_service');
    $admin = $container->get('user_find_service');

    //STATIC IMAGES
    $app->get('/static/teams/{filename}', StaticFiles\GetTeamLogo::class)
        ->add(new Auth($pointsService))
        ->setOutputBuffering(false);

    $app->get('/', 'App\Controller\DefaultController:getHelp');

    $app->group('/user', function () use ($app, $pointsService): void {
        $app->post('/signup', User\Create::class);
        $app->post('/login', User\Login::class);
        $app->get('/{username}', User\GetOne::class)->add(new Auth($pointsService));
        $app->post('/recover/{username}', User\Recover::class);
        $app->post('/validate-token/{token}', User\ValidateToken::class);
        $app->post('/reset', User\Reset::class);
    });

    $app->group('/guess', function () use ($app): void {
        $app->get('', Guess\GetUserLastNext::class);
        $app->post('/lock/{id}', Guess\Lock::class);
    })->add(new Auth($pointsService));
    
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
        $app->get('', UserParticipation\GetAll::class);
    })->add(new Auth($pointsService));

    $app->get('/p-cup-group/{id}', PPCupGroup\GetOne::class)->add(new Auth($pointsService));

    $app->get('/p-round/{id}', PPRound\GetOne::class)->add(new Auth($pointsService));
    
    $app->group('/motd', function () use ($app): void {
        $app->get('', MOTD\GetLatest::class);
        $app->post('/lock', MOTD\Lock::class);
    })->add(new Auth($pointsService));

    
    $app->post('/email-preferences', EmailPreferences\Update::class)->add(new Auth($pointsService));

    $app->group('/stats', function () use ($app): void {
        $app->get('/best-users', Stats\BestUsers::class);
        $app->get('/last-preso', Stats\LastPreso::class);
    })->add(new Auth($pointsService));

    $app->group('/admin', function () use ($app): void {

        $app->group('/user',  function() use($app): void {
            $app->get('', User\AdminGetAll::class);
        });

        
        $app->post('/p-cup/{id}', PPCup\Create::class);
        $app->get('/p-cup', PPCup\GetAll::class);

        $app->post('/motd/{matchId}', MOTD\AdminSet::class);

        
        $app->group('/p-league',  function() use($app): void {
            $app->get('', PPLeague\GetAll::class);
            $app->get('/available/{userId}', PPTournamentType\AdminAvailablePPLeagues::class);
        });

        $app->group('/p-tournament-types', function() use($app): void {
            $app->get('', PPTournamentType\GetAll::class);
            $app->post('', PPTournamentType\AdminCreate::class);
            $app->post('/{id}', PPTournamentType\AdminUpdate::class);
        });

        $app->group('/p-round-match', function() use($app): void {
            $app->post('/swap/{id}', PPRoundMatch\Swap::class);
            $app->post('/{ppRoundId}', PPRoundMatch\Create::class);
            $app->delete('/{id}', PPRoundMatch\Delete::class);
        });

        $app->group('/match', function() use($app): void {
            $app->get('', Match\GetAll::class);
            $app->get('/pick/{id}', Match\AdminPick::class);
            $app->post('/{id}', Match\Verify::class);
            $app->delete('/{id}', Match\AdminDelete::class);
        });

        $app->group('/league', function() use($app): void {
            $app->get('', League\GetAll::class);
            $app->post('', League\Create::class);
            $app->get('/need-data', League\AdminGetNeedData::class);
            $app->get('/{id}', League\GetOne::class);
            $app->post('/{id}', League\Update::class);
            $app->post('/fetch/{id}', League\Fetch::class);
        });
        
    })->add(new Auth($pointsService, $admin));

    $app->group('/cron', function () use ($app): void {
        $app->get('/fetch-football', Cron\Start::class);
        $app->get('/send-lock-reminders', Cron\ReminderLock::class);
        $app->get('/pick-motd', Cron\PickMotd::class);
    })->add(new InternalRequest());


    // Catch-all route to serve a 404 Not Found page if none of the routes match
    // NOTE: make sure this route is defined last
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
        $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
        return $handler($req, $res);
    });

    return $app;
};
