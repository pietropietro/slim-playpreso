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

        $updateData = array();
        if(isset($data->ls_suffix)){
            $updateData['ls_suffix'] = $data->ls_suffix;
        }
        if(isset($data->use_match_ls_suffix)){
            $updateData['use_match_ls_suffix'] = $data->use_match_ls_suffix;
        }

        $this->getUpdateLeagueService()->update($leagueId, $updateData);
        return $this->jsonResponse($response, "success", true, 200);
    }
}
