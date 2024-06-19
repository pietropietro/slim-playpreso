<?php

declare(strict_types=1);

namespace App\Controller\PPLeague;

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

        $userId = $this->getAndValidateUserId($request);

        $ppLeagueId = (int) $args['id'];
        $ppLeague = $this->getPPLeagueFindService()->getOne($ppLeagueId);
        
        if(!$ppLeague){
            throw new \App\Exception\NotFound('P-League Not Found.', 404);
        }

        //specific data only added when loading p-league detail
        $top_up =  $this->getPPTournamentTypeFindService()->getUps(
            $ppLeague['ppTournamentType']['id']
        );
        if($top_up)$ppLeague['ppTournamentType']['top_up'] = $top_up[0];

        $ppLeague['ppTournamentType']['userUps'] = $this->getPPTournamentTypeFindService()->getUps(
            $ppLeague['ppTournamentType']['id'], $userId, null
        );


        $ppLeague['userParticipations'] = $this->getUserParticipationFindService()->getForTournament('ppLeague_id', $ppLeagueId);
        foreach ($ppLeague['userParticipations'] as &$participation) {
            $participation['user']=$this->getUserFindService()->getOne($participation['user_id']);
        }

        $ppLeague['ppRounds'] = $this->getPPRoundFindService()->getForTournament(
            'ppLeague_id',
            $ppLeagueId,
            userId: $this->getUserParticipationFindService()->isUserInTournament($userId, 'ppLeague_id', $ppLeagueId) ? $userId : null
        );
         
        return $this->jsonResponse($response, 'success', $ppLeague, 200);
    }
}
