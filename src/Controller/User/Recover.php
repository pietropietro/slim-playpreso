<?php

declare(strict_types=1);

namespace App\Controller\User;

use Slim\Http\Request;
use Slim\Http\Response;
use App\Middleware\Auth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class Recover extends Base
{
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {    
        if(!$user = $this->getFindUserService()->getOneFromUsername((string) $args['username'], true)){
            throw new \App\Exception\User('Invalid user.', 400);
        }        

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = $_SERVER['DEBUG'];                       //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $_SERVER['SMTP'];                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $_SERVER['EMAIL_USER'];                     //SMTP username
            $mail->Password   = $_SERVER['EMAIL_PASSWORD'];                            //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('pietro@playpreso.com', 'PLAYPRESO'. ($_SERVER['DEBUG'] ? '-DEBUG' : ''));
            $mail->addAddress($user['email']);     //Add a recipient
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');
            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'playpreso password reset';

            $newPassword = substr(md5((string)rand()), 0, 7);
            $mail->Body    = 'please find your new password below: <br><b>'.$newPassword.'</b>';
            $mail->AltBody = 'please find your new password: '.$newPassword;

            $mail->send();
            $message = "please check your email inbox (and junk folder)";
            $code=200;
        } catch (Exception $e) {
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $code=500;
        }

        return $this->jsonResponse($response, 'success', $message, $code);
    }
}
