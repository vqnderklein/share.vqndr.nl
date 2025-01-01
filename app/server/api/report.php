<?php

include("../services/databaseQueries.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

if (!isset($_COOKIE['id'])) {
    echo json_encode(["Error" => "Not authorized!"]);
    exit();
}

$SQL = "SELECT * FROM `shared_files` WHERE `user_id` = ?";
$parameters = [
    0 => $_COOKIE['id']
];

$result = databaseActions($SQL, $parameters);

echo json_encode($result);