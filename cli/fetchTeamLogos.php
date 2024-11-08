<?php

declare(strict_types=1);

require __DIR__ . '/../src/App/App.php'; // Bootstrap the Slim app

// Access the container or specific services as needed
$container = $app->getContainer();

$importLogoService = $container->get('external_api_importteamlogo_service'); 
$httpClientService = $container->get('httpclient_service'); 
$externalApiSessionRepository = $container->get('externalapisession_repository'); 
$guzzleClient = $container->get('guzzle_client');

$db = $container['db'];
$saved = getSavedTeamLogos();
$teamIds = getMissingIdsForMonth($saved, $db);

if($_SERVER['DEBUG']){
    // Limit the array to the first x items for testing
    $teamIds = array_slice($teamIds, 0, 10);
}

$db->where('id', $teamIds, 'IN');
$missing = $db->get('teams',null, ['id', 'ls_id']);
echo('Teams for the month count: '.count($teamIds).PHP_EOL);

if(count($teamIds)==0)return;

$externalSessionString = getUpdatedSessionString($httpClientService, $externalApiSessionRepository, $missing[0]['ls_id']);
if(!$externalSessionString)return;

$pool2Urls = [];

// Pool 1: Fetch logo URLs
$requestsForLogoUrl = function ($teams) use ($guzzleClient, $externalSessionString) {
    foreach ($teams as $team) {
        $teamUrl = getTeamUrl($externalSessionString, $team['ls_id']);
        if(!$teamUrl)continue;
        yield function () use ($guzzleClient, $teamUrl) {
            return $guzzleClient->getAsync($teamUrl);
        };
    }
};

// Pool 2: Fetch actual logo data
$requestsForLogoData = function ($pool2Urls) use ($guzzleClient) {
    foreach ($pool2Urls as $teamId => $logoUrl) {
        yield function () use ($guzzleClient, $logoUrl, $teamId) {
            return $guzzleClient->getAsync($logoUrl)->then(
                function (GuzzleHttp\Psr7\Response $response) use ($teamId) {
                    handleLogoDataResponse($response, $teamId);
                },
                'handleRequestError'
            );
        };
    }
};

// Function to handle logo URL response
function handleTeamUrlResponse(GuzzleHttp\Psr7\Response $response, $team)
{

    global $pool2Urls; // Access the logo data requests pool

    $decoded = json_decode((string)$response->getBody());
    $logoUrl = $decoded->pageProps->initialData->basicInfo->badge->high;
    if(!$logoUrl)return;
    $parts = explode('high/', $logoUrl);
    $logoSuffix = $parts[1] ?? null;

    if ($logoSuffix) {
        $pool2Urls[$team['id']] = $_SERVER['EXTERNAL_STATIC_BASE_URI'].$logoSuffix;
    } else {
        error_log("Invalid logo URL structure for team ID {$team['id']}.\n");
    }
}



// Function to handle logo data response
function handleLogoDataResponse(GuzzleHttp\Psr7\Response $response, $teamId)
{
    global $importLogoService; // Ensure access to your logo service
    $imageData = $response->getBody()->getContents();
    $importLogoService->saveTeamLogo($imageData, $teamId);
    echo "--Logo saved for team ID {$teamId}\n";
}


// Error handler
function handleRequestError(RequestException $e)
{
    error_log("Request failed: " . $e->getMessage());
}

echo "Starting pool 1\n";
// Execute Pool 1 - Get Logo URLs
$pool1 = new GuzzleHttp\Pool($guzzleClient, $requestsForLogoUrl($missing), [
    'concurrency' => 5,
    'fulfilled' => function (GuzzleHttp\Psr7\Response $response, $index) use ($missing) {
        $team = $missing[$index];  // Access the team based on the index
        handleTeamUrlResponse($response, $team);
    },
    'rejected' => function (RequestException $reason, $index) use ($missing) {
        $team = $missing[$index];
        handleRequestError($reason, $team);
    }
]);

$pool1->promise()->wait();

echo "*************All logo URL requests completed.\n";
echo "Starting pool 2\n";
// Execute Pool 2 - Get Logo Data
if(count($pool2Urls)==0) return;
$pool2 = new GuzzleHttp\Pool($guzzleClient, $requestsForLogoData($pool2Urls), [
    'concurrency' => 5,
]);
$pool2->promise()->wait();

echo "All logo data fetched and saved.\n";



function getUpdatedSessionString($httpClientService, $externalApiSessionRepository, int $external_test_team_id){
    echo("--Getting updated session string.\n");
    $oldSessionString = $externalApiSessionRepository->getSession() ?? 'cicciozzo';
    $testTeamUrl = getTeamUrl($oldSessionString, $external_test_team_id);   
    $response = $httpClientService->getSync($testTeamUrl);
    if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 302) {  
        $decoded = json_decode((string)$response->getBody());
        //CHECK IF TOKEN EXPIRED
        if(isset($decoded->pageProps->__N_REDIRECT)){
            // Extract the new URL from the 301 json response
            $url = $decoded->pageProps->__N_REDIRECT;
            // Use a regular expression to extract the 'buildid' value
            preg_match('/buildid=([\w]+)/', $url, $matches);
            $newSession = $matches[1] ?? null;
            if(!$newSession) exit(1);
            $externalApiSessionRepository->updateSession($newSession);
        }
    }
    $session = $externalApiSessionRepository->getSession();
    echo("--Return session string: $session \n");
    return $session;
}


function getTeamUrl($session, $external_team_id){
    $teamUrl = $_SERVER['EXTERNAL_API_TEAM_URI_BASE'].$session.$_SERVER['EXTERNAL_API_TEAM_URI_BODY'].$external_team_id.$_SERVER['EXTERNAL_API_TEAM_URI_SUFFIX'];
    return $teamUrl;
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


