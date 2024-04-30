<?php

declare(strict_types=1);

namespace App\Controller\League;

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
        $id = (int) $args['id'];
        $league = $this->getLeagueFindService()->getOne(id: $id, admin: true);
        $league['last_matches'] = $this->getMatchFindService()->adminGetForLeague(leagueId: $id, next: false);
        $league['next_matches'] = $this->getMatchFindService()->adminGetForLeague(leagueId: $id, next: true);

        return $this->jsonResponse($response, 'success', $league, 200);
    }
}
