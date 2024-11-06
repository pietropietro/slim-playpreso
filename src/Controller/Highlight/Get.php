<?php

declare(strict_types=1);

namespace App\Controller\Highlight;

use Slim\Http\Request;
use Slim\Http\Response;

final class Get extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        // $userId = $this->getAndValidateUserId($request);

        $trophies = $this->getTrophyFindService()->getLatestTrophies();
        foreach ($trophies as &$trophy) {
            $trophy['user'] = $this->getUserFindService()->getOne($trophy['user_id']);
        }

       
        $presosSummaries = $this->getHighlightsPresosService()->getLastPresos(8);


        $fullPresoRounds = $this->getPPRoundFindService()->getFullPresoRound(null, 3);
        foreach ($fullPresoRounds as &$ppRound) {
            $ppRound['user'] = $this->getUserFindService()->getOne($ppRound['user_id']);
        }

        $returnArray = array(
            "trophies" => $trophies, 
            "preso" => $presosSummaries,
            "fullPresoRounds" => $fullPresoRounds
        );

        return $this->jsonResponse($response, 'success', $returnArray, 200);
    }
}
