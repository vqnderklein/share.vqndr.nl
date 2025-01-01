<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("formatDate.php");
require_once("functions.php");
require_once("../config.php");

require_once  '../lib/PHPMailer/src/Exception.php';
require_once  '../lib/PHPMailer/src/PHPMailer.php';
require_once  '../lib/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function SendMessageToReceiver($fileList, $sender, $receiver, $message, $id, $title)
{
    ob_start();
    include '../template/send.html';
    $body = ob_get_clean();

    $body = str_replace('{{SENDER}}', $sender, $body);
    $body = str_replace('{{TITLE}}', $title, $body);
    $body = str_replace('{{SEND-ID}}', $id, $body);
    $body = str_replace('{{SENDER-MESSAGE}}', $message, $body);
    $body = str_replace('{{NUMBER-OF-FILES}}', (count($fileList) > 1) ? count($fileList) . " bestanden" : count($fileList) . " bestand", $body);
    $body = str_replace('{{EXPIRE-DATE}}', returnFormattedDate(), $body);
    $body = str_replace('{{DOWNLOAD_LINK}}', "https://share.vqndr.nl/download/$id", $body);
    $body = str_replace('{{TOTAL SIZE}}', getTotalFileSize($fileList), $body);
    $body = str_replace('{{FILE_LIST_FORMAT}}', getFileListFormat($fileList), $body);

    $mail = new PHPMailer(true);
    $server_mail = "noreply@share.vqndr.nl";

    try {
        // Server settings
        $mail->isSMTP();                                      
        $mail->Host = 'smtp.mailersend.net';                  
        $mail->SMTPAuth = true;                               
        $mail->Username = USERNAME;        
        $mail->Password = PASSWD;               
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   
        $mail->Port = 587;                                    

        // Sender info
        $mail->setFrom($server_mail, 'share.vqndr.nl');
        $mail->addReplyTo($server_mail, 'share.vqndr.nl');

        // Recipient
        $mail->addAddress($receiver);                         

        // Content
        $mail->isHTML(true);                                  
        $mail->Subject = explode('@', $sender)[0] . " heeft je " . $title . " gestuurd";
        $mail->Body = $body;

        // Send the email
        $mail->send();

    } catch (Exception $e) {
        echo json_encode(["message" => "Failed to send email. Error: " . $mail->ErrorInfo]);
    }
}
