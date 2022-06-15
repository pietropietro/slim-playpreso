<?php

declare(strict_types=1);

namespace App\Controller\PPLeagueType;

use Slim\Http\Request;
use Slim\Http\Response;

final class Find extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $input = (array) $request->getParsedBody();
        $userId = $this->getAndValidateUserId($input);
        $typeId = (int) $args['id'];

        if(!$this->getPPLeagueTypeService()->isAllowed($userId, $typeId)){
            throw new Exception\User("user not allowed", 401);
        }
        
        $ppLT = $this->getPPLeagueTypeService()->getOne($typeId);

        return $this->jsonResponse($response, 'success', $ppLT, 200);
    }
}
