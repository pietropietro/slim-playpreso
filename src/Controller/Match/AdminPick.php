<?php

declare(strict_types=1);

namespace App\Controller\Match;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminPick extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $ppTournamentTypeId = (int) $args['id'];

        $matchesRaw = $this->getPickMatchService()->pick($ppTournamentTypeId, 3);
        
        $ids= array();
        foreach ($matchesRaw as $value) {
            array_push($ids, $value['id']);
        }

        $returnObj = array(
            'matches' =>  $this->getMatchFindService()->adminGet(ids: $ids),
            'leagues' =>  $this->getFindLeagueService()->getForPPTournamentType($ppTournamentTypeId)
        );

        return $this->jsonResponse($response, 'success', $returnObj, 200);
    }
}
