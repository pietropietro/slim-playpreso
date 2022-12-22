<?php

declare(strict_types=1);

namespace App\Controller\PPRound;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetOne extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $ppRoundId = (int) $args['id'];
        $ppRound = $this->getPPRoundFindService()->getOne($ppRoundId, withGuesses: true);
         
        return $this->jsonResponse($response, 'success', $ppRound, 200);
    }
}
