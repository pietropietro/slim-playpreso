<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();
$userNotificationRepository = $container->get('usernotification_repository');

echo('deleting old notifications.'.PHP_EOL);
$result = $userNotificationRepository->deleteOld();

print_r($result);

