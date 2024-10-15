<?php

declare(strict_types=1);

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Service\BaseService;

final class Mailer extends BaseService{

    public static function send(array $addresses, $subject, $body, &$error){
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
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;           
            $mail->Port       = 587;                                    //use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('pietro@playpreso.com', 'PLAYPRESO'. ($_SERVER['DEBUG'] ? '-DEBUG' : ''));

            foreach ($addresses as $addr) {
                $mail->addAddress($addr);     //Add a recipient
            }

            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');
            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;

            $newPassword = substr(md5((string)rand()), 0, 7);
            $mail->Body = $body;
            // $mail->AltBody = 'please find your new password: '.$newPassword;
            return $mail->send();
        } catch (Exception $e) {
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }
}