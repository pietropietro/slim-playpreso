<?php

declare(strict_types=1);

namespace App\Controller\User;

use Slim\Http\Request;
use Slim\Http\Response;
use App\Service\StoPasswordReset;

final class Reset extends Base
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
        
        if (!isset($data->password) || !isset($data->token)) {
            throw new \App\Exception\User('missing required fields', 400);
        }

        if(!StoPasswordReset::isTokenValid($data->token)){
            throw new \App\Exception\User('Invalid token.', 400);
        }        
        $hash = StoPasswordReset::calculateTokenHash($data->token);
        $user = $this->getUserRecoverService()->getUserFromToken($hash);
        
        $userId = (int) $args['id'];
        // if(!$user || $user['id'] != $userId){
        //     throw new \App\Exception\User('Corrupted', 400);
        // }
        
        $result = $this->getUpdateUserService()->resetPassword($userId, $data->password);
        if($result){
            $this->getUserRecoverService()->deleteTokens($userId);
        }   

        return $this->jsonResponse($response, "success", $result, 200);
    }
}
