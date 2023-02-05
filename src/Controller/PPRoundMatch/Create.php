<?php

declare(strict_types=1);

namespace App\Controller\PPRoundMatch;

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

        $ppRoundId = (int) $args['ppRoundId'];
        $ppRound = $this->getPPRoundFindService()->getOne($ppRoundId);

        if(!$ppRound){
            throw new \App\Exception\NotFound('ops', 400);
        }

        $tournamentColumn = $ppRound['ppLeague_id'] ? 'ppLeague_id' : 'ppCupGroup_id';

        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        if (!isset($data->newMatchId)) {
            throw new \App\Exception\User('missing required fields', 400);
        }


        $result = $this->getPPRoundMatchCreateService()->create(
            $ppRoundId, 
            (int)$data->newMatchId, 
            $tournamentColumn, 
            $ppRound[$tournamentColumn]
        );
                 
        return $this->jsonResponse($response, "success", $result, 200);
    }
}
