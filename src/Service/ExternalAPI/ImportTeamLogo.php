<?php

declare(strict_types=1);

namespace App\Service\ExternalAPI;

use GuzzleHttp\Client;
use App\Service\BaseService;
use App\Service\HttpClientService;

final class ImportTeamLogo extends BaseService{
    public function __construct(
        protected HttpClientService $httpClientService
    ){}

    public function fetchAndSave(string $external_logo_suffix, int $internal_team_id){
        $req_url = $external_logo_suffix;

        try {
            $response = $this->httpClientService->get(
                $req_url, 
                ['base_uri' => $_SERVER['EXTERNAL_STATIC_BASE_URI']]
            );
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $imageData = $response->getBody()->getContents();
                $imagePath = $_ENV['STATIC_IMAGE_FOLDER'] . 'teams/' . $internal_team_id . '.png';
        
                // Save the image to disk
                file_put_contents($imagePath, $imageData);
                return true;
    
            }
        } catch (\Throwable $t) {
            error_log($t->getMessage());
            return false;
        }

    }

}