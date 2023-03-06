<?php

declare(strict_types=1);

namespace App\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

final class DefaultController extends BaseController
{
    private const API_VERSION = '1.39d' ;

    public function getHelp(Request $request, Response $response): Response
    {
        $url = $this->container->get('settings')['app']['domain'];
        // $endpoints = [
        //     'users' => $url . '/api/v1/users',
        //     'status' => $url . '/status',
        // ];
        $message = [
            // 'endpoints' => $endpoints,
            'version' => self::API_VERSION,
            'timestamp' => time(),
            // 'your_ip' => $_SERVER['REMOTE_ADDR']
        ];

        return $this->jsonResponse($response, 'success', $message, 200);
    }

    public function getStatus(Request $request, Response $response): Response
    {
        $status = [
            'stats' => $this->getDbStats(),
            'MySQL' => 'OK',
            'Redis' => $this->checkRedisConnection(),
            'version' => self::API_VERSION,
            'timestamp' => time(),
        ];

        return $this->jsonResponse($response, 'success', $status, 200);
    }


    private function getDbStatus()
    {
        $repo = $this->container->get('league_repository');
        return $repo->get();
    }

    private function checkRedisConnection(): string
    {
        $redis = 'Disabled';
        if (self::isRedisEnabled() === true) {
            $redisService = $this->container->get('redis_service');
            $key = $redisService->generateKey('test:status');
            $redisService->set($key, new \stdClass());
            $redis = 'OK';
        }

        return $redis;
    }
}
