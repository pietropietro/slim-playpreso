<?php

declare(strict_types=1);

namespace App\Controller\MOTD;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetLatest extends Base
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

        $standings = $this->getMotdFindService()->getWeeklyStandings($userId);
        
        $MOTDs = $this->getMotdFindService()->getLatestMotds($userId);
        $MOTDppTT = $this->getPPTournamentTypeService()->getMOTDType();

        $returnArray = array("motds" => $MOTDs, "standings" => $standings, "ppTournamentType" => $MOTDppTT);
        return $this->jsonResponse($response, 'success', $returnArray, 200);
    }
}
