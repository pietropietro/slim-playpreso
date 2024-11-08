<?php

declare(strict_types=1);
require __DIR__ . '/../src/App/App.php';

// Access the container and services
$container = $app->getContainer();
$leagueFindService = $container->get('league_find_service');
$importService = $container->get('external_api_importleaguedata_service');
$guzzleClient = $container->get('guzzle_client');

$leagues = getLeaguesForParams($leagueFindService, $argc, $argv[1], $argv[2], $argv[3]);

if(count($leagues)==0)return;

foreach ($leagues as $league) {
    if(!$league['ls_suffix'])continue;
    $requests = function ($leagues) use ($importService) {
        foreach ($leagues as $league) {
            if (!$league['ls_suffix']) continue;
            $url = $importService->buildUrl($league['ls_suffix']);    
            // Yield each request as a new Guzzle request instance
            yield new GuzzleHttp\Psr7\Request('GET', $url);
        }
    };
}

$pool = new GuzzleHttp\Pool($guzzleClient, $requests($leagues), [
    'concurrency' => 5,
    'fulfilled' => function ($response, $index) use ($leagues, $importService) {
        // Handle successful response
        $league = $leagues[$index];
        $decodedData = json_decode((string) $response->getBody());
        
        $import_result = $importService->elaborateResponse($decodedData, $league['id']);
        echo  $league['id'].", .".$league['name'].", import result: \n";
        print_r($import_result);
    },
    'rejected' => function ($reason, $index) use ($leagues) {
        // Handle failed request
        $league = $leagues[$index];
        error_log("Failed to fetch data for league: {$league['name']} (ID: {$league['id']}) - {$reason}\n");
    },
]);


// Initiate the pool and wait for all requests to complete
$promise = $pool->promise();
$promise->wait();
echo "All requests completed.\n";


function getLeaguesForParams($leagueFindService, $argc, $arg1, $arg2, $arg3){
    // Check if the required parameters are passed
    if ($argc < 1) {
        error_log("Usage: php importFootball.php <future> <havingGuesses> <fromTime>");
        exit(1);
    }

    // Assigning parameters from command line arguments
    $future = (bool) $arg1;
    $havingGuesses = (bool) $arg2;
    $fromTime = isset($arg3) ? (string) $arg3 : null;

    if($future){
        $leagues = $leagueFindService->getNeedFutureData();
    } else {
        // Check if the required parameters are passed
        if ($argc < 2) {
            error_log("Usage: php importFootball.php <future> <havingGuesses> <fromTime>");
            exit(1);
        }
        $leagues = $leagueFindService->getNeedPastData($havingGuesses, $fromTime);
    }
    echo(count($leagues).' leagues to fetch.'.PHP_EOL); 
    return $leagues;
}