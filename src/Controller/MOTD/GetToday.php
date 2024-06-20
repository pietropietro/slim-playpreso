<?php

declare(strict_types=1);

namespace App\Controller\MOTD;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetToday extends Base
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
        
        $motd = $this->getMotdFindService()->getMotd(null,$userId);
        if(!$motd){
            throw new \App\Exception\NotFound('MOTD Not Found.', 404);
        }


        $motdLeader = $this->getMotdLeaderService()->getMotdLeader();
        $motdLeader['user'] = $this->getUserFindService()->getOne($motdLeader['user_id']);

        $motdPPtt = $this->getPPTournamentTypeFindService()->getMOTDType();

        //if motd.guess is null, insert a dummy one with verified_at being if user can or can't lock
        if(!$motd['guess']){
            $motd['guess'] = array(
                'home'=> null,
                'away' => null,
                'ppTournamentType' => $motdPPtt,
                'verified_at' => $this->getMatchFindService()->isBeforeStartTime($motd['match']['id']) ? null : 'cantlock'
            );
        }

        $returnArray = array(
            "motd" => $motd, 
            "motdLeader" => $motdLeader,
            "ppTournamentType" => $motdPPtt
        );

        return $this->jsonResponse($response, 'success', $returnArray, 200);
    }
}
