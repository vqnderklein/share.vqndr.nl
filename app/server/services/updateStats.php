<?php

header("Access-Control-Allow-Origin: https://share.vqndr.nl");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

error_reporting(E_ALL);
ini_set('display_errors', 1);

function updateStats($id)
{
    require_once("databaseQueries.php");
    require_once("feedback-sender.php");

    $sql = "UPDATE shared_files SET downloads = downloads + 1 WHERE transfer_id = ?";

    $parameters = [
        0 => $id
    ];

    databaseActions($sql, $parameters);

    $sql = "SELECT downloads FROM shared_files WHERE transfer_id = ?";

    $response =  databaseActions($sql, $parameters);
     
    if ($response[0]['downloads'] === 1) 
        TransferIsDownloaded($id);
}