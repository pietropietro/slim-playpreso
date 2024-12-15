<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();

// Get your specific service
$yearWrappedService = $container->get('stats_calculate_year_wrapped_service'); // Use the actual service name
$statsRepository = $container->get('stats_repository'); // Use the actual service name

// Determine the year: use the argument if provided, fallback to the current year
$year = (int) ($argv[1] ?? date('Y'));

try {
    $statsRepository->deleteWrapped($year);
    // Execute the specific method for calculating the "Year Wrapped" data
    $yearWrappedService->start($year); 
} catch (Exception $e) {
    // Handle any exceptions
    error_log($e->getMessage());
}