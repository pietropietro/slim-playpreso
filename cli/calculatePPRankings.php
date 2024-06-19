<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();

// Get your specific service
$calculateService = $container->get('ppranking_calculate_service'); // Use the actual service name

try {
    // Execute the specific method for calculating the "Year Wrapped" data
    $calculateService->calculate(); 
} catch (Exception $e) {
    // Handle any exceptions
    error_log($e->getMessage());
}