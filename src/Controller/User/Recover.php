<?php

declare(strict_types=1);

namespace App\Controller\User;

use Slim\Http\Request;
use Slim\Http\Response;
use App\Service\StoPasswordReset;
use App\Service\Mailer;

final class Recover extends Base
{
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {    
        if(!$user = $this->getFindUserService()->getOneFromUsername((string) $args['username'], true)){
            throw new \App\Exception\User('Invalid username', 400);
        }        

        //CREATE AND SAVE TOKEN
        StoPasswordReset::generateToken($tokenForLink, $tokenHashForDatabase);
        $this->getUserRecoverService()->saveRecoverToken($user['id'], $tokenHashForDatabase);

        //SEND TOKEN
        $subject = "Your password reset link";
        $body = "Follow this link to reset your playpreso password <br><br>"
        .$_ENV['APP_DOMAIN']."/recover/".$tokenForLink.
        "<br><br>see you";
        Mailer::send(array($user['email']), $subject, $body, $emailerror);

        if($emailerror){
            return $this->jsonResponse($response, 'error', 'something went wrong', 500);
        }
        
        return $this->jsonResponse($response, 'success', 'check your email inbox', 200);
    }
}
