<?php
chdir(__DIR__);

require_once("databaseQueries.php");
require_once("../config.php");
require_once("feedback-sender.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

if (php_sapi_name() == 'cli') {
    $key = null;
    foreach ($argv as $arg) {
        if (strpos($arg, 'k=') === 0) {
            $key = substr($arg, 2); 
            break;
        }
    }
    if ($key !== CLEAN_KEY) {
        echo json_encode(["Error" => "Invalid Key"]);
        exit();
    }
} else {
   die(json_encode(["Error" => "Invalid Key"]));
}

$allowedTimeStart = new DateTime('00:00');
$allowedTimeEnd = new DateTime('00:02');
$currentTime = new DateTime();

if ($currentTime < $allowedTimeStart || $currentTime > $allowedTimeEnd) {
    die(json_encode(["Error" => "Access Denied"]));
}

$reminderQuery = "SELECT transfer_id 
                  FROM shared_files 
                  WHERE DATE(expire_date) = DATE(NOW() + INTERVAL 2 DAY)
                  AND downloads = 0";


$reminderData = databaseActions($reminderQuery, $parameters = []);

foreach ($reminderData as $transfer) {
    TransferNoDownloadsWillSoonExpire($transfer["transfer_id"]);
}

$requireQuery = "SELECT transfer_id
                 FROM shared_files 
                 WHERE DATE(expire_date) = DATE(NOW() - INTERVAL 1 DAY)";

$result = databaseActions($requireQuery, $parameters = []);


foreach ($result as $file) {
    $id = $file["transfer_id"];
    unlink("../../../../$id" . "_encrypted.zip");
}

$deleteQuery = "DELETE FROM shared_files WHERE DATE(expire_date) = DATE(NOW() - INTERVAL 1 DAY)";

databaseActions($deleteQuery, $parameters = []);