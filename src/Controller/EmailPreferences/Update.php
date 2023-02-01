<?php

declare(strict_types=1);

namespace App\Controller\EmailPreferences;

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

        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);

        if (!isset($data->lock_reminder)) {
            throw new \App\Exception\User('missing required fields', 400);
        }

        $userId = $this->getAndValidateUserId($request);
        
        $arraydata = (array) $data;
        unset($arraydata['JWT_decoded']);

        $result = $this->getUpdateEmailPreferencesService()->update($userId, $arraydata);
                 
        return $this->jsonResponse($response, "success", $result, 200);
    }
}
