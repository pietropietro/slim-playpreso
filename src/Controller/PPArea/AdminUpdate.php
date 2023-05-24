<?php

declare(strict_types=1);

namespace App\Controller\PPArea;

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

        $ppAreaId = (int) $args['id'];
        if(!$ppAreaId) throw new \App\Exception\NotFound('Invalid request.', 400);

        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        $updateData = array();
        if(isset($data->name)){
            $updateData['name'] = $data->name;
        }
        if(!$updateData) throw new \App\Exception\NotFound('Invalid request.', 400);

        $result = $this->getPPAreaUpdateService()->update($ppAreaId, $updateData);

        return $this->jsonResponse($response, "success", $result, 200);
    }
}
