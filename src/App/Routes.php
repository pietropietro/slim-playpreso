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
use App\Controller\PPArea;
use App\Controller\EmailPreferences;
use App\Controller\PushNotificationPreferences;
use App\Controller\DeviceToken;
use App\Controller\Stats;
use App\Controller\MOTD;
use App\Controller\StaticFiles;
use App\Controller\UserNotification;
use App\Controller\Highlights;
use App\Controller\PPRanking;
use App\Middleware\Auth;
use App\Middleware\Cors;
use App\Middleware\VersionCheck;
use App\Middleware\Maintenance;


return function ($app){

    
    $app->add(function($req, $res, $next) {
        $res = (new Cors())($req, $res); // before
        $res = $next($req, $res); // route handler
        return $res;
    });

    //middleware check on frontend version
    $app->add(new VersionCheck());
    $app->add(new Maintenance());


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
        $app->delete('', User\Delete::class)->add(new Auth($pointsService));
        $app->get('/{username}', User\GetOne::class)->add(new Auth($pointsService));
        $app->post('/recover/{username}', User\Recover::class);
        $app->post('/validate-token/{token}', User\ValidateToken::class);
        $app->post('/reset', User\Reset::class);
    });

    $app->group('/guess', function () use ($app): void {
        $app->get('', Guess\GetUserCurrent::class);
        $app->get('/extra-data/{id}', Guess\ExtraData::class);
        $app->get('/team/{id}', Guess\GetForTeam::class);
        $app->get('/league/{id}', Guess\GetForLeague::class);
        $app->get('/user/{id}', Guess\GetForUser::class);
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
        $app->get('/p-leagues/{id}', UserParticipation\PPLeagues::class);
        $app->get('/p-cup-groups/{id}', UserParticipation\PPCupGroups::class);
        $app->get('', UserParticipation\GetAll::class);
    })->add(new Auth($pointsService));

    $app->get('/p-cup-group/{id}', PPCupGroup\GetOne::class)->add(new Auth($pointsService));

    $app->group('/p-round', function () use ($app): void {
        $app->get('/{id}', PPRound\GetOne::class);
        $app->get('/user-participation/{id}', PPRound\GetForUserParticipation::class);
    })->add(new Auth($pointsService));

    
    $app->group('/motd', function () use ($app): void {
        $app->get('', MOTD\GetToday::class);
        $app->get('/chart', MOTD\GetChart::class);
        // $app->get('', MOTD\GetLatest::class);
        $app->post('/lock', MOTD\Lock::class);
    })->add(new Auth($pointsService));

    $app->group('/device-token', function () use ($app): void {
        $app->post('/save', DeviceToken\Save::class);
        $app->post('/remove', DeviceToken\Remove::class);
    })->add(new Auth($pointsService));


    $app->group('/email-preferences', function () use ($app): void {
        $app->post('', EmailPreferences\Update::class);
        $app->get('', EmailPreferences\Get::class);
    })->add(new Auth($pointsService));

    $app->group('/push-notification-preferences', function () use ($app): void {
        $app->get('', PushNotificationPreferences\Get::class);
        $app->post('/toggle', PushNotificationPreferences\Toggle::class);
    })->add(new Auth($pointsService));


    $app->get('/p-ranking', PPRanking\Get::class)->add(new Auth($pointsService));
    
    $app->group('/highlights', function () use ($app): void {
        $app->get('', Highlights\Get::class);
        $app->get('/preso', Highlights\LatestPreso::class);        
    })->add(new Auth($pointsService));


    $app->group('/stats', function () use ($app): void {
        $app->get('/best-users', Stats\BestUsers::class);
        $app->get('/user/{id}', Stats\User::class);
        $app->get('/wrapped', Stats\GetWrapped::class);
    })->add(new Auth($pointsService));

    $app->group('/user-notification', function () use ($app): void {
        $app->get('', UserNotification\GetUnread::class);
    })->add(new Auth($pointsService));

    $app->group('/admin', function () use ($app): void {

        $app->group('/user',  function() use($app): void {
            $app->get('', User\AdminGetAll::class);
        });

        $app->group('/p-cup',  function() use($app): void {
            $app->post('/{id}', PPCup\Create::class);
            $app->get('', PPCup\GetAll::class);
            $app->get('/{id}', PPCup\AdminGetOne::class);    
        });

        $app->group('/p-cup-group',  function() use($app): void {
            $app->get('/{id}', PPCupGroup\AdminGetOne::class);    
        });

        $app->post('/motd/{matchId}', MOTD\AdminSet::class);

        
        $app->group('/p-league',  function() use($app): void {
            $app->get('', PPLeague\AdminGetAll::class);
            $app->get('/available/{userId}', PPTournamentType\AdminAvailablePPLeagues::class);
        });

        $app->group('/p-tournament-types', function() use($app): void {
            $app->get('', PPTournamentType\GetAll::class);
            $app->post('', PPTournamentType\AdminCreate::class);
            $app->post('/{id}', PPTournamentType\AdminUpdate::class);
        });

        $app->group('/p-area', function() use($app): void {
            $app->get('', PPArea\AdminGetAll::class);
            $app->post('', PPArea\AdminCreate::class);
            $app->post('/{id}', PPArea\AdminUpdate::class);
            $app->post('/country/{id}', PPArea\AddCountry::class);
            $app->delete('/country/{id}/{country}', PPArea\RemoveCountry::class);
            $app->post('/league/{id}', PPArea\AddLeague::class);
            $app->delete('/league/{id}/{leagueId}', PPArea\RemoveLeague::class);
        });

        $app->group('/p-round', function() use($app): void {
            $app->post('/{tournamentId}', PPRound\AdminCreate::class);
            // $app->delete('/{id}', PPRound\Delete::class);
        });

        $app->group('/p-round-match', function() use($app): void {
            $app->post('/swap/{id}', PPRoundMatch\Swap::class);
            $app->post('/{ppRoundId}', PPRoundMatch\Create::class);
            $app->delete('/{id}', PPRoundMatch\Delete::class);
        });

        $app->group('/match', function() use($app): void {
            // $app->get('/week', Match\AdminWeek::class);
            $app->get('', Match\AdminGet::class);
            $app->get('/month', Match\AdminMonth::class);
            $app->get('/pick/{id}', Match\AdminPick::class);
            $app->post('/{id}', Match\Verify::class);
            $app->delete('/{id}', Match\AdminDelete::class);
        });

        $app->group('/league', function() use($app): void {
            $app->get('', League\AdminGetAll::class);
            $app->post('', League\Create::class);
            $app->get('/countries', League\AdminGetLeagueCountries::class);
            $app->get('/need-past-data', League\AdminGetNeedPastData::class);
            $app->get('/need-future-data', League\AdminGetNeedFutureData::class);
            $app->get('/{id}', League\AdminGetOne::class);
            $app->post('/{id}', League\Update::class);
            $app->post('/fetch/{id}', League\Fetch::class);
        });
        
    })->add(new Auth($pointsService, $admin));

    // Catch-all route to serve a 404 Not Found page if none of the routes match
    // NOTE: make sure this route is defined last
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
        $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
        return $handler($req, $res);
    });

    return $app;
};
