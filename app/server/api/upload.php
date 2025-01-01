<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_COOKIE['id'])) {
    exit();
}

require_once("../services/generateLongId.php");
require_once("../services/encrypting.php");
require_once("../services/notify.php");
require_once("../services/streamManager.php");
require_once("../services/functions.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_sender = isset($_POST['email_sender']) ? $_POST['email_sender'] : '';
    $email_retriever = isset($_POST['email_retriever']) ? $_POST['email_retriever'] : '';
    $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
    $message = isset($_POST['message']) ? $_POST['message'] : '';

    if (isset($_FILES['files'])) {
        $uploadedFiles = $_FILES['files'];

        $service_id = GenerateIdentifierLong(10);

        mkdir("../../../../uploads/" . $service_id);

        $fileListArray = [];

        for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
            $fileName = $uploadedFiles['name'][$i];
            $fileTmpPath = $uploadedFiles['tmp_name'][$i];
            $fileSize = $uploadedFiles['size'][$i];
            $fileError = $uploadedFiles['error'][$i];

            $fileListArray[$i] = [
                "name" => $fileName,
                "size" => $fileSize,
                "type" => $uploadedFiles['type'][$i],
            ];

            sendSSE(['status' => "uploading"]);

            if ($fileError === UPLOAD_ERR_OK) {
                $uploadDir = '../../../../uploads/' . $service_id . "/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $destination = $uploadDir . basename($fileName);
                if (move_uploaded_file($fileTmpPath, $destination)) {
                } else {
                    echo "Failed to upload: $fileName\n";
                }
            } else {
                echo "Error with file: $fileName. Code: $fileError\n";
            }
        }

        sendSSE(['status' => "zipping"]);

        //Zip file
        $zip = new ZipArchive;
        $zipFilePath = "../../../../uploads/" . $service_id . ".zip";

        $pathdir = "../../../../uploads/" . $service_id . "/"; 

        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {

            if (is_dir($pathdir)) {
                $dir = opendir($pathdir);
                while ($file = readdir($dir)) {
                    if (is_file($pathdir . $file)) {
                        $zip->addFile($pathdir . $file, $file);
                    }
                }
                closedir($dir);
            } else {
                echo "Directory does not exist: " . $pathdir;
            }
            $zip->close();
        } else {
            echo "Failed to create ZIP file.";
        }

        //Remove normal folder

        deleteDirectory("../../../../uploads/" . $service_id . '/');

        //Encrypt upload
        sendSSE(['status' => "encrypting"]);

        encryptDirectory("../../../../uploads/", "../../../../uploads/" . $service_id . ".zip", $fileListArray, $email_sender, $email_retriever, $service_id, $subject, $message);
        
        //Send notification

        sendSSE(['status' => "sending"]);

        SendMessageToReceiver($fileListArray, $email_sender, $email_retriever, $message, $service_id, $subject);
 
        sendSSE(['status' => "done"]);
    }
   exit();
}

echo "Invalid request method.";
