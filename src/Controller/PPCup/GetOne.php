<?php

declare(strict_types=1);

namespace App\Controller\PPCup;

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
        $ppCupId = (int) $args['id'];
        $ppCup = $this->getFindCupService()->getOne($ppCupId);
        $ppCup['ppCupType'] = $this->getCupTypeService()->getOne($ppCup['ppCupType_id']);
        $ppCup['levels'] = $this->getFindCupService()->getLevels($ppCupId);
        // $ppLeague['userParticipations'] = $this->getParticipationService()->getTournamentParticipations('ppLeague_id', $ppLeagueId);
        // $ppLeague['ppRounds'] = $this->getPPRoundService()->getAll('ppLeague_id', $ppLeagueId);
         
        return $this->jsonResponse($response, 'success', $ppCup, 200);
    }
}
