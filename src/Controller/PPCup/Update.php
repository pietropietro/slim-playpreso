<?php

declare(strict_types=1);

namespace App\Controller\PPCup;

use Slim\Http\Request;
use Slim\Http\Response;

final class Update extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $id = (int) $args['id'];
        $userIdLogged = $this->getAndValidateUserId($request);
        
        // $this->checkUserPermissions($id, $userIdLogged);
        if(!$this->getCupCountService()->updateGroups($id)){
            throw new Exception("Error Processing Request", 1);
        }
        return $this->jsonResponse($response, 'success', true, 200);
    }
}
