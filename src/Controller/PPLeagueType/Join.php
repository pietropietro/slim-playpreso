<?php

declare(strict_types=1);

namespace App\Controller\PPLeagueType;

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
        $input = (array) $request->getParsedBody();
        $userId = $this->getAndValidateUserId($input);
        $typeId = (int) $args['id'];
        $okIds = $this->getPPLeagueTypeService()->getAvailableIds($userId);
        if(in_array($typeId, $okIds)){
            $ppLeagueType = $this->getJoinService()->join($typeId, $userId);
            return $this->jsonResponse($response, 'success', $ppLeagueType, 200);
        }
        throw new Exception\User("can't join", 401);
    }
}
