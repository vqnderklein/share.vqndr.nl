<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../services/databaseQueries.php");
require_once("../services/functions.php");

header("content-type: application/json");

$id = $_GET['id'] ?? ''; 

if (empty(trim($id))) { 
    echo json_encode(["access" => "denied"]);
    exit();
}

$parameters = [
    0 => $id
];

if (doesTransferExist($id)) {

    $sql = "SELECT * FROM `shared_files` WHERE transfer_id = ?";
    
    $response = databaseActions($sql, $parameters);

    echo json_encode($response);

} else {
    echo json_encode(["error" => "File is not available or already deleted."]);
}

?>