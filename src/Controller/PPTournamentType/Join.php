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
        $input = (array) $request->getParsedBody();
        $userId = $this->getAndValidateUserId($input);
        $typeId = (int) $args['id'];
        
        if(!$this->getPPTournamentTypeService()->isAllowed($userId, $typeId)){
            throw new Exception\User("user not allowed", 401);
        }

        if(!$this->getPPTournamentTypeService()->canAfford($userId, $typeId)){
            throw new Exception\User("not enough points", 401);
        }

        $ppLeague = $this->getFindPPLeagueService()->getJoinable($typeId, $userId);
        
        if(!$this->getPointsService()->minus($userId, $ppLeague['ppTournamentType']['cost'])){
            throw new Exception\User("couldn't join", 500);
        }

        if(!$insert = $this->getParticipationService()->createPPLeagueParticipation($userId, $ppLeague['id'], $typeId)){
            throw new Exception\User("something went wrong", 500);
        };

        //update ppLeague user_count
        return $this->jsonResponse($response, 'success', $ppLeague['id'], 200);
    }
}
