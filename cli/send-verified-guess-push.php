<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();
$guessRepository = $container->get('guess_repository');

$interval = isset($argv[1]) ? (string) $argv[1] : '-6 hours';

$guesses = $guessRepository->getLastVerified(
    userId: null,
    afterString: $interval,
    limit:null,
    notified: false
);

$userNotificationCreateService = $container->get('usernotification_create_service');

echo("need to send ".count($guesses)." notifications. \n");
foreach ($guesses as $key => $guess) {
    echo("i: $key \n");
    $userNotificationCreateService->create(
        $guess['user_id'],
        'guess_verified',
        $guess['id'], 
    );
}


