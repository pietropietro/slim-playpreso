<?php

declare(strict_types=1);

namespace App\Controller\PPRound;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetForUserParticipation extends Base
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

        $userParticipationId = (int) $args['id'];

        $userParticipation = $this->getUserParticipationFindService()->getOne($userParticipationId, false);
        
        $column = $userParticipation['ppLeague_id'] ? 'ppLeague_id' : 'ppCupGroup_id';
        $userCurrentRound = $this->getPPRoundFindService()->getUserCurrentRound($column, $userParticipation[$column], $userId);


        return $this->jsonResponse($response, 'success', $userCurrentRound, 200);
    }
}
