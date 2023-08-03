<?php

declare(strict_types=1);

namespace App\Controller\PPRound;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminCreate extends Base
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {   
        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        $tournamentColumn = $data->tournament . '_id'; 
        $tournamentId = (int) $args['tournamentId'];
        $ppTournamentTypeId = null;

        if($tournamentColumn === 'ppLeague_id'){
            $ppLeague = $this->getPPLeagueFindService()->getOne($tournamentId);

            if (!$ppLeague || isset($ppLeague['finished_at']))
            {
                throw new \App\Exception\NotFound("can't edit ppLeague", 400);
            }
            $ppTournamentTypeId = $ppLeague['ppTournamentType_id'];

        }else {
            //TODO handle ppcup 
            throw new \App\Exception\NotFound("invalid data", 400);
        }

        $lastRound = $this->getPPRoundFindService()->getLast($tournamentColumn, $tournamentId)['round'] ?? 0;

        $newId = $this->getPPRoundCreateService()->create(
            $tournamentColumn,
            $tournamentId,
            $ppTournamentTypeId,
            $lastRound + 1,
        );
        
        $status = $newId ? 'success' : 'error';
        return $this->jsonResponse($response, $status , $newId, 201);
    }
}
