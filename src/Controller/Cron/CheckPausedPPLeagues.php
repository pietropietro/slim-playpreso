<?php

declare(strict_types=1);

namespace App\Controller\Cron;

use Slim\Http\Request;
use Slim\Http\Response;

final class CheckPausedPPLeagues extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
    ): Response {

        $ppLeagues = $this->getPPLeaguesFindService()->adminGetAllPaused();
        $results = [];
        foreach ($ppLeagues as $ppl) {
            $lastRound = $this->getPPRoundFindService()->getLast('ppLeague_id', $ppl['id'])['round'] ?? 0;
            $res = $this->getPPRoundCreateService()->create(
                'ppLeague_id', 
                $ppl['id'], 
                $ppl['ppTournamentType_id'], 
                $lastRound + 1
            );
            array_push($results, $res);
        }
        
        return $this->jsonResponse($response, 'success', $results, 200);
    }
}
