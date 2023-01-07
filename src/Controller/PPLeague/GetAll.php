<?php

declare(strict_types=1);

namespace App\Controller\PPLeague;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetAll extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $ppTournamentTypeId = $request->getQueryParams()['ppTournamentTypeId'] ?? null;

        $ppLeagues = $this->getPPLeagueFindService()->adminGetAll($ppTournamentTypeId);
        foreach($ppLeagues as &$ppLeague){
            $ppLeague['user_count']= $this->getUserParticipationFindService()->countInTournament('ppLeague_id', $ppLeague['id']);
            if($ppLeague['started_at']){
                $ppLeague['currentRound'] = $this->getPPRoundFindService()->getCurrentRoundNumber('ppLeague_id', $ppLeague['id']);
                $ppLeague['playedInCurrentRound'] = $this->getPPRoundFindService()->verifiedInLatestRound('ppLeague_id', $ppLeague['id']);
                $ppLeague['nextMatch'] = $this->getMatchFindService()->getNextMatchInPPTournament('ppLeague_id', $ppLeague['id']);
                $ppLeague['lastMatch'] = $this->getMatchFindService()->getLastMatchInPPTournament('ppLeague_id', $ppLeague['id']);
            }

        }

        return $this->jsonResponse($response, 'success', $ppLeagues, 200);
    }
}
