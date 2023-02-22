<?php

declare(strict_types=1);

namespace App\Controller\League;

use Slim\Http\Request;
use Slim\Http\Response;

final class Create extends Base
{
    public function __invoke(Request $request, Response $response): Response
    {
        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        if (!isset($data->name) || !isset($data->tag) || !isset($data->country) 
            || !isset($data->country_level) || !isset($data->area) || !isset($data->area_level)
        ){
            throw new App/Exception/User('missing required fields', 400);
        }

        $newId = $this->getCreateLeagueService()->create(
            $data->name, 
            $data->tag,
            $data->country,
            (int) $data->country_level,
            $data->area,
            (int) $data->area_level,            
            (int) $data->parent_id ?? null,
            $data->ls_suffix ?? null
        );
        
        return $this->jsonResponse($response, 'success', $newId, 201);
    }
}
