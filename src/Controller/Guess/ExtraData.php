<?php

declare(strict_types=1);

namespace App\Controller\Guess;

use Slim\Http\Request;
use Slim\Http\Response;

final class ExtraData extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $userId = $this->getAndValidateUserId($request);

        //handle motd case
        $isMotd =  in_array($args['id'], ['motd', 'dummy']) ?? false;

        if($isMotd){
            // it's a workaround, not actually a guess
            $guess = $this->getMotdFindService()->getMotd(
                dateString: null,
                userId: $userId 
            );
        } else{
            $guessId = (int) $args['id'];
            $guess = $this->getGuessFindService()->getOne($guessId);    
        }

        $leagueStandings = $guess['match']['league']['standings'];
        $this->getTeamFindService()->addNameToStandings($leagueStandings);

        $lastFiveHome = $this->getMatchFindService()->getLastForTeam($guess['match']['home_id'], 5);
        $lastFiveAway = $this->getMatchFindService()->getLastForTeam($guess['match']['away_id'], 5);
        $lastFive = array(
            'home' => $lastFiveHome,
            'away' => $lastFiveAway,
        );

        if(!$isMotd){
            $ppTournamentType = $guess['ppTournamentType'];
            $tournamentColumn = isset($ppTournamentType['is_cup']) && $ppTournamentType['is_cup'] ? 'ppCupGroup_id' : 'ppLeague_id' ;
            $ppRound = $this->getPPRoundFindService()->getFromPPRM($guess['ppRoundMatch_id']);
            $tournamentId = $ppRound[$tournamentColumn];
            $userParticipation = $this->getUserParticipationFindService()->getOneByUserAndTournament(
                $userId, $tournamentColumn, $tournamentId
            );
        }

        $extraData = array(
            'leagueStandings' => $leagueStandings,
            'lastMatches' => $lastFive,
            'userParticipation' => $userParticipation ?? null
        );
                 
        return $this->jsonResponse($response, "success", $extraData, 200);
    }
}
