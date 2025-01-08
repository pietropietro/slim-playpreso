<?php

declare(strict_types=1);
require __DIR__ . '/../src/App/App.php';

use GuzzleHttp\RequestOptions;

// Access the container and services
$container = $app->getContainer();
$leagueFindService = $container->get('league_find_service');
$importService = $container->get('external_api_importleaguedata_service');
$guzzleClient = $container->get('guzzle_client');

$leagues = getLeaguesForParams($leagueFindService, $argc, $argv[1], $argv[2], $argv[3]);

if(count($leagues)==0)return;
$startTime = microtime(true); // Start time for total execution

foreach ($leagues as $league) {
    if(!$league['ls_suffix'])continue;
    $url = $importService->buildUrl($league['ls_suffix']);
    echo "[DEBUG] Fetching data for league: {$league['name']}, country:  {$league['country']}, ID: {$league['id']}...\n";

    try {
        // Perform HTTP request
        $response = $guzzleClient->request('GET', $url, [
            'timeout' => 30,
            'on_stats' => function (GuzzleHttp\TransferStats $stats) {
                $requestTime = $stats->getTransferTime(); // Get request time in seconds
                echo "[Request to {$stats->getEffectiveUri()}] completed in " . number_format($requestTime, 3) . " seconds.\n";
            }
        ]);

        echo "[DEBUG] Processing: {$league['id']}.\n";
        $decodedData = json_decode((string) $response->getBody());

        // Process the fetched data
        $startProcessingTime = microtime(true);
        $import_result = $importService->elaborateResponse($decodedData, $league['id']);
        $endProcessingTime = microtime(true);

        $processingTime = $endProcessingTime - $startProcessingTime;
        echo "[League ID: {$league['id']} - {$league['country']}] {$league['name']} - Import result: " .
             "created={$import_result['created']}, modified={$import_result['modified']}, verified={$import_result['verified']} " .
             "- Processing completed in " . number_format($processingTime, 3) . " seconds.\n";

    } catch (Throwable $e) {
        echo "[ERROR] Failed to fetch or process data for League ID: {$league['id']} - {$e->getMessage()}\n";
    }
}

$endTime = microtime(true); // End time for total execution
$totalTime = $endTime - $startTime;

echo "All requests and processing completed in " . number_format($totalTime, 3) . " seconds.\n";



function getLeaguesForParams($leagueFindService, $argc, $arg1, $arg2, $arg3)
{
    // Check if the required parameters are passed
    if ($argc < 1) {
        error_log("Usage: php importFootball.php <leagueIds|future> <havingGuesses> <fromTime>");
        exit(1);
    }

    // Check if the first argument is a JSON array of IDs
    $leagueIds = json_decode($arg1, true);
    if (is_array($leagueIds) && allIntegers($leagueIds)) {
        // Fetch leagues by IDs
        $leagues = $leagueFindService->adminGet($leagueIds)['leagues'];
        echo(count($leagues) . ' leagues fetched by IDs.' . PHP_EOL);
        return $leagues;
    }

    // If not IDs, proceed with the existing logic
    $future = filter_var($arg1, FILTER_VALIDATE_BOOLEAN);
    $havingGuesses = filter_var($arg2, FILTER_VALIDATE_BOOLEAN);
    $fromTime = isset($arg3) ? (string) $arg3 : null;

    if ($future) {
        $leagues = $leagueFindService->getNeedFutureData();
    } else {
        if ($argc < 2) {
            error_log("Usage: php importFootball.php <future> <havingGuesses> <fromTime>");
            exit(1);
        }
        $leagues = $leagueFindService->getNeedPastData($havingGuesses, $fromTime);
    }

    echo(count($leagues) . ' leagues to fetch.' . PHP_EOL);
    return $leagues;
}

function allIntegers(array $array)
{
    return array_reduce($array, fn($carry, $item) => $carry && is_int($item), true);
}