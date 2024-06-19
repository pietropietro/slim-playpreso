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
        $userId = $this->getAndValidateUserId($request);

        $ppCupGroup = $this->getCupGroupService()->getOne(
            $groupId, 
            enriched: true, 
            userId: $this->getUserParticipationService()->isUserInTournament($userId, 'ppCupGroup_id', $groupId) ? $userId 
                : null
        );
        
        foreach ($ppCupGroup['userParticipations'] as &$participation) {
            $participation['user']=$this->getUserFindService()->getOne($participation['user_id']);
        }


        $ppCupGroup['ppTournamentType'] = $this->getTournamentTypeService()->getOne($ppCupGroup['ppTournamentType_id']);
        
        return $this->jsonResponse($response, 'success', $ppCupGroup, 200);
    }
}
