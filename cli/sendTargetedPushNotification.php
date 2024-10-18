<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();

$pushNotificationsSendService = $container->get('pushnotifications_send_service');

if ($argc < 1) {
    error_log("Usage: php sendTargetedPushNotification.php <userId>   <title>   <body> ");
    exit(1);
}

$userId =  (int) $argv[1];
$title =  isset($argv[2]) ? $argv[2] : 'test title';
$body =  isset($argv[3]) ? $argv[2] : 'test body';

echo 'try send for user' . $userId . PHP_EOL;

try {
    // use the params inside
    $pushNotificationsSendService->send($userId, $title, $body); 
} catch (Exception $e) {
    // Handle any exceptions
    echo 'ERROR:' . PHP_EOL;
    print_r($e->getMessage());
}
