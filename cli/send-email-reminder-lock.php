<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();

$emailPreferencesFindService = $container->get('emailpreferences_find_service');
$emailBuilderLockService = $container->get('emailbuilder_lockreminder_service');
$needReminder = $emailPreferencesFindService->getNeedLockReminder();

$successCount = 0;
$failCount = 0;

echo 'need reminder count: ' . count($needReminder) . PHP_EOL;

foreach ($needReminder as $value) {
    $matchesIds = explode(',', $value['matches_id_concat']);
    $prepared =$emailBuilderLockService->prepare($value['username'], $matchesIds);
    
    // Try sending the email
    $emailSent = App\Service\Mailer::send([$value['email']], $prepared['subject'], $prepared['contentHtml'], $emailerror);    
    if ($emailSent) {
        $successCount++;
    } else {
        // Increment failure count if send operation did not return success
        $failCount++;
        error_log("Failed to send email to: " . $value['email'] . ". Error: " . $emailerror);
    }
}

// Output the result
echo 'Emails sent successfully: ' . $successCount . PHP_EOL;
echo 'Failed to send emails: ' . $failCount . PHP_EOL;