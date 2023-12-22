<?php

declare(strict_types=1);

require __DIR__ . '/slim-playpreso/src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();

// Get your specific service
$yearWrappedService = $container->get('stats_calculate_year_wrapped_service'); // Use the actual service name

try {
    // Execute the specific method for calculating the "Year Wrapped" data
    $yearWrappedService->start(2023); 
} catch (Exception $e) {
    // Handle any exceptions
    error_log($e->getMessage());
}