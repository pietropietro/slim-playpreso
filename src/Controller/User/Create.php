<?php

declare(strict_types=1);

namespace App\Controller\User;

use Slim\Http\Request;
use Slim\Http\Response;

final class Create extends Base
{
    public function __invoke(Request $request, Response $response): Response
    {
        $input = (array) $request->getParsedBody();
        if(!$result = $this->getCreateUserService()->create($input)){
            throw new \App\Exception\User("could not create", 500);
        }

        return $this->jsonResponse($response, 'success', $result, 201);
    }
}
