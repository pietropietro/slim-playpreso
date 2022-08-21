<?php

declare(strict_types=1);

namespace App\Controller\PPCupGroup;

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
        $groupId = (int) $args['id'];
        
        $ppCupGroup = $this->getCupGroupService()->getOne($groupId);
        $ppCupGroup['userParticipations'] = $this->getUserParticipationService()->getForTournament('ppCupGroup_id', $groupId);
        $ppCupGroup['ppRounds'] = $this->getPPRoundService()->getForTournament('ppCupGroup_id', $groupId);
        $ppCup = $this->getFindCupService()->getOne($ppCupGroup['ppCup_id']);
        $ppCupGroup['ppCupType'] = $this->getCupTypeService()->getOne($ppCup['ppCupType_id']);
        
        return $this->jsonResponse($response, 'success', $ppCupGroup, 200);
    }
}
