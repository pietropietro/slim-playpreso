<?php

declare(strict_types=1);

namespace App\Controller\User;

use Slim\Http\Request;
use Slim\Http\Response;

final class Reset extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
    ): Response {

        $input = (array) $request->getParsedBody();
        $data = json_decode((string) json_encode($input), false);
        
        if (!isset($data->password) || !isset($data->token)) {
            throw new \App\Exception\User('missing required fields', 400);
        }

        $userRecover = $this->getUserRecoverService()->validateToken($data->token);        
        $result = $this->getUpdateUserService()->resetPassword($userRecover['id'], $data->password);

        if($result){
            $this->getUserRecoverService()->deleteTokens($userRecover['id']);
        }   

        return $this->jsonResponse($response, "success", $result, 200);
    }
}
