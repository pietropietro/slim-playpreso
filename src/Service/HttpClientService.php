<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp;

final class HttpClientService{

    public function __construct(
        protected GuzzleHttp\Client $client
    ){}

    public function get(string $url, array $options = []): GuzzleHttp\Psr7\Response
    {
        try {
            $response = $this->client->get($url);
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

    
