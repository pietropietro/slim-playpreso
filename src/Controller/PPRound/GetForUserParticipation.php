<?php

declare(strict_types=1);

namespace App\Controller\PPRound;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetForUserParticipation extends Base
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

        $userParticipationId = (int) $args['id'];

        $userParticipation = $this->getUserParticipationFindService()->getOne($userParticipationId, false);
        
        $column = $userParticipation['ppLeague_id'] ? 'ppLeague_id' : 'ppCupGroup_id';
        $userCurrentRound = $this->getPPRoundFindService()->getUserCurrentRound(
            $column, 
            $userParticipation[$column], 
            $userParticipation['user_id']
        );


        //if currentuser is not the user we are getting the round for
        //clear sensitive data i.e. home away locks for unverified_matches
        if($userParticipation['user_id'] != $userId){
            foreach ($userCurrentRound as &$ppRound) {
                if(!$ppRound['guess']['verified_at']){
                    $ppRound['guess']['home'] = null;
                    $ppRound['guess']['away'] = null;
                }
            }
        }


        return $this->jsonResponse($response, 'success', $userCurrentRound, 200);
    }
}
