<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();
$userRepository = $container->get('user_repository');




$inactiveUsers = $userRepository->getInactive();


echo('inactive users to notify: '.PHP_EOL);
print_r($inactiveUsers);

$userNotificationCreateService = $container->get('usernotification_create_service');

    $title= '💩 YOU EARNT A NEW EMOJI 💩';
    $body = 'If you play again, this will go away!';
    
    $push_data =  array(
        'title' => $title,
        'body' =>  $body
    );


foreach ($inactiveUsers as $g) {
    $userNotificationCreateService->create(
        $g['id'],
        'inactive_user',
        null,
        push_text_data: $push_data
    );
}

