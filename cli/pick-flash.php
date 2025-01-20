<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

use App\Service\Flash\Create;
use App\Service\Flash\Find;

/** @var \Psr\Container\ContainerInterface $container */
$flashCreateService = $container->get('flash_create_service');  // or 'flash_create_service'
$flashFindService   = $container->get('flash_find_service');    // or 'flash_find_service'

// If the user typed a date, e.g. "php pick-flash.php 20-01-2025",
// we use that. Otherwise, we do your logic to check tomorrow or the day after last flash.
$dateArg = $argv[1] ?? null;

if (!$dateArg) {
    // No date provided; let's figure out what date to pick for.

    // 1) Build tomorrow's date in d-m-Y
    $tomorrowDateArg = date('d-m-Y', strtotime('+1 day'));
    // 2) Also the ISO for the same day
    $tomorrowIso = date('Y-m-d', strtotime('+1 day'));

    // Check if there's flash for tomorrow
    $hasFlashTomorrow = $flashFindService->hasFlashForDate($tomorrowIso);

    if (!$hasFlashTomorrow) {
        // If no flash for tomorrow, pick for tomorrow
        $dateArg = $tomorrowDateArg;
    } else {
        // Otherwise, find the last flash overall and pick the next day after that
        $lastFlashRow = $flashFindService->getLastFlash();
        if ($lastFlashRow) {
            $lastFlashDateTime = new DateTime($lastFlashRow['date_start']);
            // We'll produce the "next day" in d-m-Y format
            $lastFlashDateTime->modify('+1 day');
            $dateArg = $lastFlashDateTime->format('d-m-Y');
        } else {
            // If no last flash found at all, default to tomorrow
            $dateArg = $tomorrowDateArg;
        }
    }
}

// Now do the pick
$chosenMatchIds = $flashCreateService->pickForDate($dateArg);

echo "Chosen " . count($chosenMatchIds) . " flash matches for $dateArg\n";
if (!empty($chosenMatchIds)) {
    echo "Match IDs: " . implode(', ', $chosenMatchIds) . "\n";
} else {
    echo "No matches selected.\n";
}
