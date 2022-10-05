<?php

declare(strict_types=1);

namespace App\Controller\PPCup;

use Slim\Http\Request;
use Slim\Http\Response;

final class Create extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        $ppCup = $this->getCreateCupService()->create($data->ppTournamentType_id, $data->slug);
        return $this->jsonResponse($response, 'success', $ppCup, 200);
    }
}
