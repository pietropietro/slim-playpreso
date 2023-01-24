<?php

declare(strict_types=1);

namespace App\Controller\PPTournamentType;

use Slim\Http\Request;
use Slim\Http\Response;
use \App\Exception;

final class Join extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $userId = $this->getAndValidateUserId($request);
        $typeId = (int) $args['id'];

        if(!$this->getCheckPPTournamentService()->check($userId, $typeId)){
            throw new Exception\User("user not allowed", 403);
        }
        
        if(!$id = $this->getJoinPPTournamentTypeService()->joinAvailable($userId, $typeId)){
            throw new Exception\NotFound("could not join", 500);
        }

        return $this->jsonResponse($response, 'success', $id, 200);
    }
}
