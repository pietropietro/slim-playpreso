<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();
$motdFindService = $container->get('motd_find_service');
$matchPickerService = $container->get('match_picker_service');
$motdCreateService = $container->get('motd_create_service');

if($motdFindService->hasMotd()){
    echo('there is already a motd, not picking new one.');
    return;
}

$match = $matchPickerService->adminPickForToday(limit: 1);
if(!$match){
    echo('no match found');
    return;
};

$newPPRMid = $motdCreateService->create($match['id']);
if(!$newPPRMid){
    echo('error in creating ppRoundMatch..');
    return;
}

echo('created ppRoundMatch for motd, id:'. PHP_EOL . $newPPRMid. PHP_EOL . 'match_id:' . $match['id']);

