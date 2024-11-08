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

    public function fetchOneAndSave(string $external_logo_suffix, int $internal_team_id){
        $req_url = $_SERVER['EXTERNAL_STATIC_BASE_URI'].$external_logo_suffix;

        try {
            $response = $this->httpClientService->getSync($req_url);
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $imageData = $response->getBody()->getContents();

                $this->saveTeamLogo($imageData, $internal_team_id);
            }
        } catch (\Throwable $t) {
            error_log($t->getMessage());
            return false;
        }

    }


    public function saveTeamLogo($imageData, $internal_team_id){
        $imagePath = $_SERVER['STATIC_IMAGE_FOLDER'] . 'teams/' . $internal_team_id . '.png';
        // Save the image to disk
        file_put_contents($imagePath, $imageData);
        return true;
    }

}