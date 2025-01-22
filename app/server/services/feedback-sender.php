<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function TransferNoDownloadsWillSoonExpire($id) {

    require_once("databaseQueries.php");

    $SQL = "SELECT * FROM shared_files WHERE transfer_id = ?";

    $parameters = [
        0 => $id
    ];

    $result = databaseActions($SQL, $parameters);
    $subject = "Je transfer verloopt bijna";
    $message = "Je transfer met de titel '<b>" . strtolower($result[0]['transfer_title']) . "</b>' verloopt over 2 dagen ({{EXPIRE-DATE}}) en is nog niet gedownload. Over 2 dagen worden de bestanden automatisch van onze server verwijderd. Indien de bestanden nog niet zijn gedownload, zul je ze opnieuw moeten versturen. ";
    MailToSender($id, $subject, $result[0]['user'], $message, $result[0]['expire_date'], json_decode($result[0]['transfer_files']), $result[0]['transfer_title'], $result[0]['transfer_title'] . " verloopt bijna");

}

function TransferIsDownloaded($id)
{
    require_once("databaseQueries.php");

    $SQL = "SELECT * FROM shared_files WHERE transfer_id = ?";

    $parameters = [
        0 => $id
    ];

    $result = databaseActions($SQL, $parameters);
    $subject = "Je bestanden zijn gedownload!";
    $message = "Je bestanden zijn gedownload, je krijgt een eenmalige e-mail van updates over je transfer, kijk voor meer en actuele updates in het account dashboard.";
    MailToSender($id, $subject, $result[0]['user'], $message, $result[0]['expire_date'], json_decode($result[0]['transfer_files']), $result[0]['transfer_title'],  $result[0]['transfer_title'] . " is gedownload!");
}

function MailToSender($id, $subject, $sender, $message, $date, $fileList, $title, $subjectMail) {
    require_once("formatDate.php");
    require_once("functions.php");
    require_once("../config.php");

    require_once  '../lib/PHPMailer/src/Exception.php';
    require_once  '../lib/PHPMailer/src/PHPMailer.php';
    require_once  '../lib/PHPMailer/src/SMTP.php';

    ob_start();
    include '../template/files-downloaded.html';
    $body = ob_get_clean();

    $body = str_replace('{{MESSAGE}}', $message, $body);
    $body = str_replace('{{SENDER}}', explode("@", $sender)[0], $body);
    $body = str_replace('{{SUBJECT-OF-MAIL}}', $subject, $body);
    $body = str_replace('{{SEND-ID}}', $id, $body);
    $body = str_replace('{{NUMBER-OF-FILES}}', (count($fileList) > 1) ? count($fileList) . " bestanden" : count($fileList) . " bestand", $body);
    $body = str_replace('{{EXPIRE-DATE}}', returnProperExpireDate($date), $body);
    $body = str_replace('{{DOWNLOAD_LINK}}', "https://share.vqndr.nl/download/$id", $body);
    $body = str_replace('{{TOTAL SIZE}}', getTotalFileSize($fileList), $body);
    $body = str_replace('{{FILE_LIST_FORMAT}}', getFileListFormat($fileList), $body);

    $mail = new PHPMailer(true);
    $server_mail = "no-reply@share.vqndr.nl";

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.smtp2go.com';
        $mail->SMTPAuth = true;
        $mail->Username = USERNAME2;
        $mail->Password = PASSWD2;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 2525;

        // Sender info
        $mail->setFrom($server_mail, 'share.vqndr.nl');
        $mail->addReplyTo($server_mail, 'share.vqndr.nl');

        // Recipient
        $mail->addAddress($sender);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subjectMail;
        $mail->Body = $body;

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        echo json_encode(["message" => "Failed to send email. Error: " . $mail->ErrorInfo]);
    }
}