<?php

declare(strict_types=1);

namespace App\Controller\PPCupGroup;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminGetOne extends Base
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

        $ppCupGroup = $this->getCupGroupService()->getOne(
            id: $groupId, 
            enriched: true, 
            userId: null
        );

        $ppCupGroup['ppTournamentType'] = $this->getTournamentTypeService()->getOne($ppCupGroup['ppTournamentType_id']);
        
        return $this->jsonResponse($response, 'success', $ppCupGroup, 200);
    }
}
