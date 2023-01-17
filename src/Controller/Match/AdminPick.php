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
        $all_matches_raw = $this->getPickMatchService()->nextMatchesForPPTournamentType($ppTournamentTypeId);
        $ids = array_column($matchesRaw, 'id');
        $all_ids = array_column($all_matches_raw, 'id');

        $returnObj = array(
            'all_matches' =>  $this->getMatchFindService()->adminGet(ids: $all_ids),
            'picked_matches' =>  $this->getMatchFindService()->adminGet(ids: $ids),
            'leagues' =>  $this->getFindLeagueService()->getForPPTournamentType($ppTournamentTypeId)
        );

        return $this->jsonResponse($response, 'success', $returnObj, 200);
    }
}
