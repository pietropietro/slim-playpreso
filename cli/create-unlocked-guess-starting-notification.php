<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();
$guessRepository = $container->get('guess_repository');


$interval = isset($argv[1]) ? (string) $argv[1] : '+6 hours';


$guesses = $guessRepository->getUnlockedGuessesStarting(
    interval: $interval,
    withoutUserNotification: true
);

echo('guesses to notify: '.PHP_EOL);
print_r($guesses);

$userNotificationCreateService = $container->get('usernotification_create_service');

foreach ($guesses as $g) {
    $userNotificationCreateService->create(
        $g['user_id'],
        'guess_unlocked_starting',
        $g['id'], 
    );
}

