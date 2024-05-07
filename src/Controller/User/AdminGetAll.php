<?php

declare(strict_types=1);

namespace App\Controller\User;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminGetAll extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $page = (int) $request->getQueryParam('page', 1); // Default to page 1
        $limit = (int) $request->getQueryParam('limit', null); // Default limit to 50


        //data{'users': [], 'total':22}
        if(!$data = $this->getFindUserService()->adminGet($page, $limit)){
            throw new \App\Exception\User('no users', 404);
        }

        return $this->jsonResponse($response, 'success', $data, 200);
    }
}
