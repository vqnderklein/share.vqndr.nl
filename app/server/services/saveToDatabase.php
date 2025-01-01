<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);

function saveToDatabase($array, $sender, $receiver, $transfer_id, $key, $iv, $title, $message) {
    require_once("../config.php");
    global $link;
    
    $id = 1;
    $downloads = 0;
    $send_date = date("Y-m-d");
    $expire_date = date("Y-m-d", strtotime("+2 weeks"));
    $format_array = json_encode($array);

    $stmt = $link->prepare("INSERT INTO shared_files (id, transfer_id, transfer_files, user, email_receiver, send_date, expire_date, downloads, decrypt_key, decrypt_iv, transfer_title, user_id, transfer_message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssisssis", $id, $transfer_id, $format_array, $sender, $receiver, $send_date, $expire_date, $downloads, $key, $iv, $title, $_COOKIE['id'], $message);
    
    $stmt->execute();
    $stmt->close();
    $link->close();

}


?>