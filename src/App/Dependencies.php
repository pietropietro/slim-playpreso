<?php

declare(strict_types=1);

use App\Handler\ApiError;
use App\Service\RedisService;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/../vendor/mysqli.php';
$container['db'] = new MysqliDb(
    $database['host'],
    $database['user'],
    $database['pass'],
    $database['name'],
    $database['port']
);
$database = $container->get('settings')['db'];

$container['errorHandler'] = static fn (): ApiError => new ApiError();

$container['redis_service'] = static function ($container): RedisService {
    $redis = $container->get('settings')['redis'];

    return new RedisService(new \Predis\Client($redis['url']));
};

$container['notFoundHandler'] = static function () {
    return static function ($request, $response): void {
        throw new \App\Exception\NotFound('Route Not Found.', 404);
    };
};
