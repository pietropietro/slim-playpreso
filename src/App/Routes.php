<?php

declare(strict_types=1);

use App\Controller\Guess;
// use App\Controller\Match;
use App\Controller\User;
use App\Middleware\Auth;

return function ($app) {
    $app->get('/', 'App\Controller\DefaultController:getHelp');
    $app->get('/status', 'App\Controller\DefaultController:getStatus');
    $app->post('/login', \App\Controller\User\Login::class);

    // $app->group('/api/v1', function () use ($app): void {
        // $app->group('/guesses', function () use ($app): void {
        //     $app->post('', Guess\Create::class);
        //     $app->get('/{id}', Guess\GetOne::class);
        //     $app->put('/{id}', Guess\Lock::class);
        // })->add(new Auth());

        $app->group('/users', function () use ($app): void {
            $app->post('', User\Create::class);
            $app->get('/{id}', User\GetOne::class)->add(new Auth());
            $app->put('/{id}', User\Update::class)->add(new Auth());
            // $app->delete('/{id}', User\Delete::class)->add(new Auth());
        });

        // $app->group('/matches', function () use ($app): void {
        //     $app->get('', Match\GetAll::class);
        //     $app->post('', Match\Create::class);
        //     $app->get('/{id}', Match\GetOne::class);
        //     $app->put('/{id}', Match\Update::class);
        //     // $app->delete('/{id}', Match\Delete::class);
        // });
    // });  

    return $app;
};
