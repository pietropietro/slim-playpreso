<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
$baseDir = __DIR__ . '/../../';
$dotenv = Dotenv\Dotenv::createImmutable($baseDir);
$envFile = $baseDir . '.env';
if (file_exists($envFile)) {
    $dotenv->load();
}
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT']);

//TODO keep?
error_reporting(E_ALL);
ini_set('display_errors', '1');

$playpreso_version = '1.0.0';

$settings = require __DIR__ . '/Settings.php';
$app = new \Slim\App($settings);
// $app->add(new \CorsSlim\CorsSlim());
$container = $app->getContainer();
require __DIR__ . '/Dependencies.php';
require __DIR__ . '/Services.php';
require __DIR__ . '/Repositories.php';
(require __DIR__ . '/Routes.php')($app);
