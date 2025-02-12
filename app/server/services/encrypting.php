<?php

include('saveToDatabase.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

function encryptDirectory($dirPath, $zipFilePath, $array, $sender, $receiver, $id, $title, $message, $totalSize)
{
    $totalFileSizeMB = $totalSize / (1024 * 1024);

    if ($totalFileSizeMB > 150) {
        // No Encryption

        saveToDatabase($array, $sender, $receiver, $id, null, null, $title, $message);
    } else if ($totalFileSizeMB <= 150) {
        // Encryption
       
        $key = openssl_random_pseudo_bytes(32);
        $iv = openssl_random_pseudo_bytes(16);
    
        $zipContent = file_get_contents($zipFilePath);
    
        $encryptedData = openssl_encrypt($zipContent, 'aes-256-cbc', $key, 0, $iv);
    
        $encryptedFilePath = pathinfo($zipFilePath, PATHINFO_DIRNAME) . '/' . pathinfo($zipFilePath, PATHINFO_FILENAME) . '_encrypted.zip';
        file_put_contents($encryptedFilePath, $encryptedData);
    
        unlink($zipFilePath);
    
        $encrypedKeys = encryptTheDecryptKeys($key, $iv);
    
        saveToDatabase($array, $sender, $receiver, $id, $encrypedKeys['key'], $encrypedKeys['iv'], $title, $message);
    }
}

function encryptTheDecryptKeys($key, $iv)
{
    require_once("../config.php");

    $encryptedKey = openssl_encrypt($key, 'aes-256-cbc', MASTER, 0, substr(MASTER, 0, 16));
    $encryptedIV = openssl_encrypt($iv, 'aes-256-cbc', MASTER, 0, substr(MASTER, 0, 16));

    return [
        "key" => $encryptedKey,
        "iv" => $encryptedIV,
    ];
}
