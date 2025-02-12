<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../services/databaseQueries.php");
require_once("../services/decrypting.php");
require_once("../services/downloadSupplier.php");
require_once("../services/functions.php");

header("content-type: application/json");

$mode = $_GET['m'];
$transfer_id = $_GET['i'];

if (empty(trim($transfer_id))) { 
    echo json_encode(["access" => "denied"]);
    exit();
}

if (!doesTransferExist($transfer_id)) {
    echo json_encode(["Error" => "There are no transfers with that id."]);
    exit();
}

if ($mode == "file") {
    $encodedFileName = $_GET['f'];
    $fileName = urldecode($encodedFileName);

    $keys = DatabaseDecrypting($transfer_id);

    $iv = $keys['iv'] ? $keys['iv'] : null;
    $key = $keys['key'] ? $keys['key'] : null;

    getFileFromDirectory($transfer_id, $iv, $key, "../../../../uploads/$transfer_id" . "_encrypted.zip", $fileName);
} else if ($mode == "zip") {

    $keys = DatabaseDecrypting($transfer_id);

    getDirToUser($transfer_id, $keys['iv'], $keys['key']);

}

?>