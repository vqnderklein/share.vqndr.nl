<?php

function getFileFromDirectory($id, $iv, $key, $path, $file)
{
    require_once("../services/decrypting.php");
    require_once("../services/functions.php");
    require_once("../services/updateStats.php");

    //Ontsleutel dir

    decryptDirectory($path, $iv, $key, $id);

    //Get file

    $file = ExtractSpecificFile($file, "../../../../uploads/$id-decrypted.zip", "../../../../uploads/$id-temp");

    //Download to user

    downloadFileToUser($file);
    UpdateStats($id);

    //Remove temp folder

    deleteDirectory("../../../../uploads/$id-temp");
    unlink("../../../../uploads/$id-decrypted.zip");
}

function getDirToUser($id, $iv, $key)
{
    require_once("../services/decrypting.php");
    require_once("../services/functions.php");
    require_once("../services/updateStats.php");

    //Ontsleutel dir

    decryptDirectory("../../../../uploads/$id" . "_encrypted.zip", $iv, $key, $id);

    //Rename dir

    mkdir("../../../../uploads/$id", 0777);
    rename("../../../../uploads/$id-decrypted.zip", "../../../../uploads/$id/transfer.zip");

    //Download folder to user

    downloadFileToUser("../../../../uploads/$id/transfer.zip");
    UpdateStats($id);

    //Cleanup
    deleteDirectory("../../../../uploads/$id/");
    unlink("../../../../uploads/$id-decrypted.zip");
}
