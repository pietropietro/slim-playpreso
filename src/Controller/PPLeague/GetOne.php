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
        $ppLeague = $this->getPPLeagueFindService()->getOne($ppLeagueId);
        $ppLeague['userParticipations'] = $this->getUserParticipationFindService()->getForTournament('ppLeague_id', $ppLeagueId);
        $ppLeague['ppRounds'] = $this->getPPRoundFindService()->getForTournament(
            'ppLeague_id',
            $ppLeagueId,
            userId: $this->getUserParticipationFindService()->isUserInTournament($userId, 'ppLeague_id', $ppLeagueId) ? $userId : null
        );
         
        return $this->jsonResponse($response, 'success', $ppLeague, 200);
    }
}
