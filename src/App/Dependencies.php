<?php

declare(strict_types=1);

use App\Handler\ApiError;
use App\Service\RedisService;
use Psr\Container\ContainerInterface;

$database = $container->get('settings')['db'];
$container['db'] = new MysqliDb(
    $database['host'],
    $database['user'],
    $database['pass'],
    $database['name'],
    $database['port']
);


$container['guzzle_client'] = static fn(ContainerInterface $container): GuzzleHttp\Client => new GuzzleHttp\Client([
    'timeout'  => 10.0,
    'proxy'    => $_SERVER['PROXY_URL'] ?? null,
]);

// Path to your .p8 APNs authentication file
// Your Apple Developer team ID
// Your app's bundle ID
// Your APNs key ID 

$authProvider = Pushok\AuthProvider\Token::create([
    'key_id' => $_SERVER['APNS_KEY_ID'],
    'team_id' => $_SERVER['APNS_TEAM_ID'],
    'app_bundle_id' => $_SERVER['APNS_BUNDLE_ID'],
    'private_key_path' => $_SERVER['APNS_KEY_FILE'],
]);

// Clients for push notifications
$environment = $_SERVER['DEBUG'] ? false : true;
$container['apns_client'] = new Pushok\Client($authProvider, $environment);
$firebaseServiceAccount = $_SERVER['FCM_SERVICE_ACCOUNT'];
$container['firebase_messaging'] = (new Kreait\Firebase\Factory)
    ->withServiceAccount($firebaseServiceAccount)
    ->createMessaging();



$container['errorHandler'] = $container['phpErrorHandler'] = static fn (): ApiError => new ApiError();

$container['redis_service'] = static function ($container): RedisService {
    $redis = $container->get('settings')['redis'];

    return new RedisService(new \Predis\Client($redis['url']));
};

$container['notFoundHandler'] = static function () {
    return static function ($request, $response): void {
        throw new \App\Exception\NotFound('Route Not Found.', 404);
    };
};
