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
        
        $motdPPRM = $this->getMotdFindService()->getMotd(null,$userId);
        if(!$motdPPRM){
            throw new \App\Exception\NotFound('MOTD Not Found.', 404);
        }

        $motdPPRM['tot_locks'] = $this->getPPRoundMatchFindService()->countPPRMGuesses($motdPPRM['id']);


        $motdChart = $this->getMotdLeaderService()->getChart(1,3);
        
        foreach ($motdChart['chart'] as &$motdStanding) {
            $motdStanding['user'] = $this->getUserFindService()->getOne($motdStanding['user_id']);
        }

        $motdPPtt = $this->getPPTournamentTypeFindService()->getMOTDType();

        //if motd.guess is null, insert a dummy one with verified_at being if user can or can't lock
        if(!$motdPPRM['guess']){
            $motdPPRM['guess'] = $this->getGuessCreateService()->buildDummyGuess($userId, $motdPPRM['id']);
        }

        $motdPPRM['guess']['ppTournamentType'] = $motdPPtt;

        $returnArray = array(
            "motd" => $motdPPRM, 
            "motdChart" => $motdChart,
            "ppTournamentType" => $motdPPtt
        );

        return $this->jsonResponse($response, 'success', $returnArray, 200);
    }
}
