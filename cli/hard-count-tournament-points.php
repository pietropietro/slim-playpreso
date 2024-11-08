<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();

// Get your specific service
$upService = $container->get('userparticipation_update_service'); 

// Check if the required parameters are passed
if ($argc < 3) {
    error_log("Usage: php nameofscript.php <tournament_column> <tournament_id>");
    exit(1);
}

// Assigning parameters from command line arguments
$tournamentColumn = $argv[1];
$ppTournamentId = (int) $argv[2];

try {
    // use the params inside
    $upService->update($tournamentColumn, $ppTournamentId); 
} catch (Exception $e) {
    // Handle any exceptions
    error_log($e->getMessage());
}