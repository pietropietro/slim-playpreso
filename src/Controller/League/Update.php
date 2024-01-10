<?php

declare(strict_types=1);

namespace App\Controller\League;

use Slim\Http\Request;
use Slim\Http\Response;

final class Update extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $leagueId = (int) $args['id'];

        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        if(!$data->name || !$data->tag) {
            throw new \App\Exception\NotFound('missing required fields', 400);
        }

        $updateData = array_intersect_key(
            // the array with all keys
            get_object_vars($data),  
            // keys to be extracted
            array_flip(['name', 'tag', 'country', 'level', 'ls_suffix', 'ls_410', 'parent_id'])
        );

        $this->getUpdateLeagueService()->update($leagueId, $updateData);
        return $this->jsonResponse($response, "success", true, 200);
    }
}
