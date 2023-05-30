<?php

declare(strict_types=1);

namespace App\Controller\PPTournamentType;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminCreate extends Base
{
    public function __invoke(Request $request, Response $response): Response
    {
        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        if (!isset($data->name) || !isset($data->cost) || !isset($data->emoji) || !isset($data->rgb)
        ){
            throw new \App\Exception\NotFound('missing required fields', 400);
        }

        $newId = $this->getPPTournamentTypeCreateService()->create(
            $data->name, 
            (int) $data->cost,
            $data->rgb,
            $data->emoji,
            isset($data->level) ? (int) $data->level : null,
            isset($data->rounds) ? (int) $data->rounds : null,
            isset($data->participants) ? (int) $data->participants : null,
            isset($data->pick_country) ? (string) $data->pick_country : null,
            isset($data->pick_area) ? (int) $data->pick_area : null,
            isset($data->pick_league) ? (int) $data->pick_league : null,
        );
        
        $status = $newId ? 'success' : 'error';
        return $this->jsonResponse($response, $status , $newId, 201);
    }
}
