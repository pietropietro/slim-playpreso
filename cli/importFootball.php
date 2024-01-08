<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();

// Get your specific service
$leagueFindService = $container->get('league_find_service'); 
$importService = $container->get('external_api_importleaguedata_service'); 

// Check if the required parameters are passed
if ($argc < 2) {
    error_log("Usage: php importFootball.php <future> <havingGuesses> <fromTime>");
    exit(1);
}

// Assigning parameters from command line arguments
$future = (bool) $argv[1];
$havingGuesses = (bool) $argv[2];
$fromTime = isset($argv[3]) ? (string) $argv[3] : null;


if($future){
    $leagues = $leagueFindService->getNeedFutureData();
} else {
    $leagues = $leagueFindService->getNeedPastData($havingGuesses, $fromTime);
}
echo(count($leagues).', starting..'.PHP_EOL); 

foreach ($leagues as $key => $league) {
    $parent_id = isset($league['parent_id']) ? $league['parent_id'] : '';
    echo('fetching '.$league['name'].', '.$league['id'].'-'. $parent_id .PHP_EOL); 
    if(!$league['ls_suffix'])continue;
    $res = $importService->fetch($league['ls_suffix'], $league['id']);
    echo('result:'.PHP_EOL);
    print_r($res);
}

echo('finished.'.PHP_EOL); 
