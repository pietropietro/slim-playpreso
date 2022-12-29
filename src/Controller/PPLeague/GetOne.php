<?php

declare(strict_types=1);

namespace App\Controller\PPLeague;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetOne extends Base
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

        $ppLeagueId = (int) $args['id'];
        $ppLeague = $this->getPPLeagueService()->getOne($ppLeagueId);
        $ppLeague['userParticipations'] = $this->getUserParticipationService()->getForTournament('ppLeague_id', $ppLeagueId);
        $ppLeague['ppRounds'] = $this->getPPRoundService()->getForTournament(
            'ppLeague_id',
            $ppLeagueId,
            userId: $this->getUserParticipationService()->isUserInTournament($userId, 'ppLeague_id', $ppLeagueId) ? $userId : null
        );
         
        return $this->jsonResponse($response, 'success', $ppLeague, 200);
    }
}
