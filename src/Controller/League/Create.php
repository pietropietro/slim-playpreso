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

        if (!isset($data->name) || !isset($data->tag)
        ){
            throw new \App\Exception\NotFound('missing required fields', 400);
        }

        $newId = $this->getCreateLeagueService()->create(
            $data->name, 
            $data->tag,
            $data->country,
            (int) $data->level,
            isset($data->parent_id) ? (int) $data->parent_id : null,
            $data->ls_suffix ?? null
        );
        
        $status = $newId ? 'success' : 'error';
        return $this->jsonResponse($response, $status , $newId, 201);
    }
}
