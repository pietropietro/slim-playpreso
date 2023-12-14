<?php

declare(strict_types=1);

namespace App\Controller\Cron;

use Slim\Http\Request;
use Slim\Http\Response;

final class CheckPausedPPTournaments extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
    ): Response {

        $ppLeagues = $this->getPPLeaguesFindService()->adminGetAllPaused();
        $ppCupGroups = $this->getPPCupGroupsFindService()->getPaused();
        
        $together = array_merge($ppLeagues, $ppCupGroups);
        $results = [];

        foreach ($together as $ppt) {
            $column = isset($ppt['ppCup_id']) ? 'ppCupGroup_id' : 'ppLeague_id';
            $lastRound = $this->getPPRoundFindService()->getLast($column, $ppt['id'])['round'] ?? 0;

            $res = $this->getPPRoundCreateService()->create(
                $column, 
                $ppt['id'], 
                $ppt['ppTournamentType_id'], 
                $lastRound + 1
            );
            array_push($results, $res);
        }
        
        return $this->jsonResponse($response, 'success', $results, 200);
    }
}
