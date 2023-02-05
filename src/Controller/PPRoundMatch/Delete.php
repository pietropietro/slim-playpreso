<?php

declare(strict_types=1);

namespace App\Controller\PPRoundMatch;

use Slim\Http\Request;
use Slim\Http\Response;

final class Delete extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $ppRoundMatchId = (int) $args['id'];

        $result = $this->getPPRoundMatchDeleteService()->delete($ppRoundMatchId);
                 
        return $this->jsonResponse($response, "success", $result, 200);
    }
}
