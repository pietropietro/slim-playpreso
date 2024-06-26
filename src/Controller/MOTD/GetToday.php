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

        $motd['tot_locks'] = $this->getPPRoundMatchFindService()->countPPRMGuesses($motd['id']);


        $motdChart = $this->getMotdLeaderService()->getChart(1,1);
        foreach ($motdChart['chart'] as &$motdStanding) {
            $motdStanding['user'] = $this->getUserFindService()->getOne($motdStanding['user_id']);
        }

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

        $motd['guess']['ppTournamentType'] = $motdPPtt;

        $returnArray = array(
            "motd" => $motd, 
            "motdChart" => $motdChart,
            "ppTournamentType" => $motdPPtt
        );

        return $this->jsonResponse($response, 'success', $returnArray, 200);
    }
}
