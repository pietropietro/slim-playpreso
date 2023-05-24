<?php

declare(strict_types=1);

namespace App\Controller\PPArea;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminCreate extends Base
{
    public function __invoke(Request $request, Response $response): Response
    {
        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        if (!isset($data->name)
        ){
            throw new \App\Exception\NotFound('missing required fields', 400);
        }

        $newId = $this->getPPAreaCreateService()->create(
            $data->name, 
        );
        
        $status = $newId ? 'success' : 'error';
        return $this->jsonResponse($response, $status , $newId, 201);
    }
}
