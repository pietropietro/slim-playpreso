<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app


$startTime = microtime(true);
$startMemory = memory_get_usage();

// Access the container or specific services as needed
$container = $app->getContainer();
$guessRepository = $container->get('guess_repository');
$interval = isset($argv[1]) ? (string) $argv[1] : '-2 hours';

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

// Calculate time and memory usage stats
$endTime = microtime(true);
$executionTime = $endTime - $startTime;
$endMemory = memory_get_usage();
$memoryUsage = $endMemory - $startMemory;
// Convert memory usage to MB
$memoryUsageInMB = $memoryUsage / (1024 * 1024);

if ($executionTime > 30) { // Alert if script takes more than 30 seconds
    error_log("ALERT: Push verified_guesses send script took more than 30 seconds to complete.");
}
if (sys_getloadavg()[0] > 5) { // Alert if 1-minute load average is above 5
    error_log("ALERT: System load is high during push send verified_guesses script.");
}
if ($memoryUsageInMB > 50) { // Alert if memory usage exceeds 50 MB
    error_log("ALERT: Push send script memory usage exceeded 50 MB.");
}