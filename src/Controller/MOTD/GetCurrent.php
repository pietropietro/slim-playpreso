<?php

declare(strict_types=1);

namespace App\Controller\MOTD;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetCurrent extends Base
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

        $motd = $this->getMotdFindService()->getCurrentMotd($userId);
        $standings = $this->getMotdFindService()->getWeeklyStandings($userId);
        $ppTournamentType = $this->getPPTournamentTypeService()->getMOTDType();

        $returnArray = array("motd" => $motd, "standings" => $standings, "ppTournamentType" => $ppTournamentType);
        return $this->jsonResponse($response, 'success', $returnArray, 200);
    }
}
