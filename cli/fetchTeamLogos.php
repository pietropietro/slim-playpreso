<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();

$importLogoService = $container->get('external_api_importteamlogo_service'); 
$db = $container['db'];

$saved = getSavedTeamLogos();
$teamIds = getMissingIdsForMonth($saved, $db);

$db->where('id', $teamIds, 'IN');
$missing = $db->get('teams',null, ['id', 'ls_id']);

foreach($missing as $team){
    //GET TEAM external overview.json and find .png path
    $logoUrl = getLogoUrl($team['ls_id'], $container);
    if(!$logoUrl) continue;
    // Split the string by 'high/'
    $parts = explode('high/', $logoUrl);
    // Get the part after 'high/', which should be at index 1
    $logoSuffix = $parts[1] ?? null;
    if(!$logoSuffix)return;
    //use logo import service to retrieve and store logo
    $importLogoService->fetchAndSave($logoSuffix, $team['id']);
}

function getLogoUrl(int $teamId, $container){
    $externalApiSessionRepository = $container->get('externalapisession_repository'); 
    $httpClientService = $container->get('http_client_service'); 

    $session = $externalApiSessionRepository->getSession() ?? 'cicciozzo';
    $url = $session.$_SERVER['EXTERNAL_API_TEAM_URI_BODY'].$teamId.$_SERVER['EXTERNAL_API_TEAM_URI_SUFFIX'];
    
    $response = $httpClientService->get(
        $url, 
        ['base_uri' => $_SERVER['EXTERNAL_API_TEAM_URI_BASE']]
    );

    if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 302) {  
        $decoded = json_decode((string)$response->getBody());

        //CHECK IF TOKEN EXPIRED
        if(isset($decoded->pageProps->__N_REDIRECT)){
            // Extract the new URL from the 301 json response
            $url = $decoded->pageProps->__N_REDIRECT;

            // Use a regular expression to extract the 'buildid' value
            preg_match('/buildid=([\w]+)/', $url, $matches);
            $newSession = $matches[1] ?? null;
            if(!$newSession) return;

            $externalApiSessionRepository->updateSession($newSession);
            getLogoUrl($teamId, $container);
        }else{
            $logoUrl = $decoded->pageProps->initialData->basicInfo->badge->high;
            return $logoUrl;
        }
    }else if($response->getStatusCode() == 404){
        //TODO save team ls_404
        return null;
    }
}


function getSavedTeamLogos() {
    $directoryPath = $_ENV['STATIC_IMAGE_FOLDER'] . 'teams/';
    $ids = [];

    // Check if the directory exists
    if (is_dir($directoryPath)) {
        // Open the directory
        if ($dirHandle = opendir($directoryPath)) {
            // Read all files in the directory
            while (($file = readdir($dirHandle)) !== false) {
                // Check if the file is a PNG image
                if (pathinfo($file, PATHINFO_EXTENSION) === 'png') {
                    // Extract the ID from the filename
                    $id = pathinfo($file, PATHINFO_FILENAME);
                    $ids[] = $id;
                }
            }
            closedir($dirHandle);
        }
    } else {
        error_log("Directory not found: " . $directoryPath);
    }

    return $ids;
}


function getMissingIdsForMonth(array $savedIds, $db){
    // Calculate the date 30 days from now
    $thirtyDaysFromNow = date('Y-m-d H:i:s', strtotime('+30 days'));

    // Construct the query to get matches within the next 30 days
    $db->where('date_start', date('Y-m-d H:i:s'), '>=');
    $db->where('date_start', $thirtyDaysFromNow, '<=');
    $matches = $db->get('matches', null, ['home_id', 'away_id']);

    // Extract team IDs while excluding the not needed ones
    $teamIds = [];
    foreach ($matches as $match) {
        if (!in_array($match['home_id'], $savedIds)) {
            $teamIds[] = $match['home_id'];
        }
        if (!in_array($match['away_id'], $savedIds)) {
            $teamIds[] = $match['away_id'];
        }
    }

    // Remove duplicate IDs
    $teamIds = array_unique($teamIds);
    return array_values($teamIds);
}


