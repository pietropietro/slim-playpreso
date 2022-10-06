<?php

declare(strict_types=1);

namespace App\Controller\PPCup;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetAll extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $ppTournamentTypeId = $request->getQueryParams()['ppTournamentTypeId'] ?? null;

        $ppCups = $this->getFindCupService()->getAll($ppTournamentTypeId);
        // $ppCup['levels'] = $this->getFindCupService()->getLevels($ppCupId);
                 
        return $this->jsonResponse($response, 'success', $ppCups, 200);
    }
}
