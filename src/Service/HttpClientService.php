<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp;

final class HttpClientService{

    public function __construct(){}

    public function get(string $url, array $options = []): GuzzleHttp\Psr7\Response
    {
        $client = new GuzzleHttp\Client([
            'base_uri' => $options['base_uri'] ?? '',
            'timeout'  => $options['timeout'] ?? 10.0,
            'proxy' => $_SERVER['PROXY_URL']
        ]);

        try {
            $response = $client->get($url);
            return $response;
        } catch (GuzzleHttp\Exception\ClientException $e) {
            error_log($e->getMessage());
            $response = $e->getResponse();
            return $response;
        } catch (GuzzleHttp\Exception\ConnectException $e) {
            error_log($e->getMessage());
        }catch (\Exception $e) {
            error_log($e->getMessage());
        } catch (\Throwable $t) {
            error_log($t->getMessage());
        }
        return new GuzzleHttp\Psr7\Response(
            404,
            [], // Headers
            "Resource not found" // Body
        );
    }
}

    
