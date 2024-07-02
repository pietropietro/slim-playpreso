<?php

declare(strict_types=1);

namespace App\Controller\Match;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminGet extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        // Get query parameters
        $params = $request->getQueryParams();

        // Extract parameters
        $country = $params['country'] ?? null;
        $leagueId = isset($params['leagueId']) ? (int) $params['leagueId'] : null;
        $from = $params['from'] ?? null;
        $to = $params['to'] ?? null;

        // If no params are provided, return an empty result to avoid too big of a result
        if (!$country && !$leagueId && !$from && !$to) {
            return $this->jsonResponse($response, 'success', [], 200);
        }

        // Get matches from the service
        $matches = $this->getMatchFindService()->adminGet(null, $country, $leagueId, $from, $to);

        foreach ($matches as &$m) {
            $m['league'] = $this->getLeagueFindService()->getOne($m['league_id'], true);
        }

        return $this->jsonResponse($response, 'success', $matches, 200);  

    }
}
