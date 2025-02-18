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
        // recover link hostname is the same for dev and prod
        //since the .well-known hosted on playpreso.com makes the app open
        $body = "Follow this link to reset your playpreso password <br><br>"
        ."https://playpreso.com/recover/".$tokenForLink.
        "<br><br><b>Note: This link will only work on mobile devices with the PlayPreso app installed.</b><br><br>xoxo,<br>pietro";
        
        Mailer::send(array($user['email']), $subject, $body, $emailerror);

        if($emailerror){
            return $this->jsonResponse($response, 'error', 'something went wrong', 500);
        }
        
        return $this->jsonResponse($response, 'success', 'check your email inbox', 200);
    }
}
