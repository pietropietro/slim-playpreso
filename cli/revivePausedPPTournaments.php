<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();

// Get your specific service
$ppLeagueFindService = $container->get('ppleague_find_service'); 
$ppCupGroupFindService = $container->get('ppcupgroup_find_service'); 
$ppRoundFindService = $container->get('ppround_find_service'); 
$ppRoundCreateService = $container->get('ppround_create_service'); 

    
$ppLeagues = $ppLeagueFindService->adminGetAllPaused();
$ppCupGroups = $ppCupGroupFindService->getPaused();

$together = array_merge($ppLeagues, $ppCupGroups);

echo(count($ppLeagues).' paused ppLeagues'.PHP_EOL); 
echo(count($ppCupGroups).' paused ppCupGroups'.PHP_EOL); 

foreach ($together as $ppt) {
    $column = isset($ppt['ppCup_id']) ? 'ppCupGroup_id' : 'ppLeague_id';
    echo($column.':'.$ppt['id'].',pptt:'.$ppt['ppTournamentType_id'].PHP_EOL); 
    $lastRound = $ppRoundFindService->getLast($column, $ppt['id'])['round'] ?? 0;

    $res = $ppRoundCreateService->create(
        $column, 
        $ppt['id'], 
        $ppt['ppTournamentType_id'], 
        $lastRound + 1
    );
    print_r($res);
}

echo('finished.'.PHP_EOL); 
