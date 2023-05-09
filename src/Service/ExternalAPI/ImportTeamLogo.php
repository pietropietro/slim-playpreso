<?php

declare(strict_types=1);

namespace App\Service\ExternalAPI;

use GuzzleHttp\Client;
use App\Service\BaseService;

final class ImportTeamLogo extends BaseService{
    public function __construct(){}

    public function fetch(string $external_logo_suffix, int $internal_team_id){
        $client = new Client([
                'base_uri' => $_SERVER['EXTERNAL_STATIC_BASE_URI'],
                'timeout'  => 10.0,
                'proxy' => $_SERVER['PROXY_URL']
            ]
        );

        $req_url = $external_logo_suffix;

        try{
            $response = $client->get($req_url);
            $imageData = $response->getBody()->getContents();

            $imagePath = $_ENV['STATIC_IMAGE_FOLDER'] . 'teams/' . $internal_team_id . '.png';
    
            // Save the image to disk
            file_put_contents($imagePath, $imageData);
            return true;
    
        } catch (\Exception $e) {
            // Handle the exception here
            return false;
        }
        
    }

}