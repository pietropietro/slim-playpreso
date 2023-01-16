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

        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        $updateData = array();
        if(isset($data->rgb)){
            $updateData['rgb'] = $data->rgb;
        }
        if(isset($data->emoji)){
            $updateData['emoji'] = $data->emoji;
        }

        $result = $this->getUpdatePPTournamentService()->update($ppTournamentTypeId, $updateData);

        return $this->jsonResponse($response, "success", $result, 200);
    }
}
