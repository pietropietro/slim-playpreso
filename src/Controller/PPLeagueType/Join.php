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
        
        if(!in_array($typeId, $okIds)){
            throw new Exception\User("user can't join", 401);
        }

        $ppLeague = $this->getPPLeagueService()->getJoinable($typeId, $userId);
        
        //TODO REMOVE POINTS FROM USER

        $insert = $this->getParticipationService()->create($userId,["ppLeague_id","ppLeagueType_id"],[$ppLeague['id'],$typeId]);
        return $this->jsonResponse($response, 'success', $insert, 200);
    }
}
