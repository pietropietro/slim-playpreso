<?php

declare(strict_types=1);

namespace App\Controller\PPLeague;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetFull extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $ppLeagueId = (int) $args['id'];
        $ppLeague = $this->getPPLeagueService()->getOne($ppLeagueId);
        $ppLeague['ppLeagueType'] = $this->getPPLeagueTypeService()->getOne($ppLeague['ppLeagueType_id']);
        $ppLeague['userParticipations'] = $this->getParticipationService()->getTournamentParticipations('ppLeague_id', $ppLeagueId);
        $ppLeague['ppRounds'] = $this->getPPRoundService()->getAllForPPL($ppLeagueId);
         
        return $this->jsonResponse($response, 'success', $ppLeague, 200);
    }
}
