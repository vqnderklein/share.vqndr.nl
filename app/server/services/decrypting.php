<?php


function decryptDirectory($path, $iv, $key, $id)
{
    $encryptedContent = file_get_contents($path);

    $decryptedData = openssl_decrypt($encryptedContent, 'aes-256-cbc', $key, 0, $iv);

    $decryptedFilePath = "../../../../uploads/$id-decrypted.zip";
    file_put_contents($decryptedFilePath, $decryptedData);
}


function decryptTheDecryptKeys($key, $iv)
{
    require_once("../config.php");

    $decryptedKey = openssl_decrypt($key, 'aes-256-cbc', MASTER, 0, substr(MASTER, 0, 16));
    $decryptedIV = openssl_decrypt($iv, 'aes-256-cbc', MASTER, 0, substr(MASTER, 0, 16));

    return [
        "key" => $decryptedKey,
        "iv" => $decryptedIV,
    ];
}
