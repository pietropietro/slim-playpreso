<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();
$guessVerifyService = $container->get('guess_verify_service'); 

$guessVerifyService->setMissed();

