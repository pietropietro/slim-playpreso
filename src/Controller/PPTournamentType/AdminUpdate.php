<?php

declare(strict_types=1);

namespace App\Controller\PPTournamentType;

use Slim\Http\Request;
use Slim\Http\Response;

final class AdminUpdate extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $ppTournamentTypeId = (int) $args['id'];
        if(!$ppTournamentTypeId) throw new \App\Exception\NotFound('Invalid request.', 400);

        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        $updateData = array();
        if(isset($data->name)){
            $updateData['name'] = $data->name;
        }
        if(isset($data->level)){
            $updateData['level'] = $data->level;
        }
        if(isset($data->rgb)){
            $updateData['rgb'] = $data->rgb;
        }
        if(isset($data->emoji)){
            $updateData['emoji'] = $data->emoji;
        }
        if(isset($data->rounds)){
            $updateData['rounds'] = $data->rounds;
        }
        if(isset($data->cost)){
            $updateData['cost'] = $data->cost;
        }
        if(isset($data->participants)){
            $updateData['participants'] = $data->participants;
        }
        $updateData['pick_country'] = $data->pick_country;
        $updateData['pick_area'] = $data->pick_area;
        $updateData['pick_tournament'] = $data->pick_tournament;
        
        if(!$updateData) throw new \App\Exception\NotFound('Invalid request.', 400);

        $result = $this->getUpdatePPTournamentService()->update($ppTournamentTypeId, $updateData);

        return $this->jsonResponse($response, "success", $result, 200);
    }
}
