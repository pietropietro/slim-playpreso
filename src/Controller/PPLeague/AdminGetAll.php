<?php

declare(strict_types=1);

namespace App\Controller\PPLeague;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminGetAll extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        // $ppTournamentTypeId = $request->getQueryParams()['ppTournamentTypeId'] ?? null;
        // $ppTournamentTypeId = (int) $ppTournamentTypeId ?? null;
        
        $paused = $request->getQueryParams()['paused'] ?? null;

        if((bool)$paused){
            $ppLeagues = $this->getPPLeagueFindService()->adminGetAllPaused();
        }else{

            $ppTournamentTypeName = $request->getQueryParams()['ppTournamentTypeName'] ?? null;
            
            $ppTournamentTypeLevel = $request->getQueryParams()['ppTournamentTypeLevel'] ?? null;
            $ppTournamentTypeLevel = isset($ppTournamentTypeLevel) ? (int) $ppTournamentTypeLevel : null;

            $finished = $request->getQueryParams()['ft'] ?? null;
            $finishedBool = $finished === 'all' ? null : ($finished === 'finished' ? true : false);

            $started = $request->getQueryParams()['st'] ?? null;
            $startedBool = $started === 'all' ? null : ($started === 'started' ? true : false);

           
            $ppLeagues = $this->getPPLeagueFindService()->get(
                $ppTournamentTypeLevel, 
                $ppTournamentTypeName, 
                $finishedBool, 
                $startedBool
            );
        }

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
