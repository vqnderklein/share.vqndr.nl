<?php

function formatBytes($bytes, $decimals = 2)
{
    if ($bytes === 0) return '0 Bytes';

    $sizeUnits = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $i = floor(log($bytes, 1024));

    return round($bytes / pow(1024, $i), $decimals) . ' ' . $sizeUnits[$i];
}

function getTotalFileSize($files)
{
    $totalSize = 0;

    foreach ($files as $file) {
        if (is_object($file)) {
            $totalSize += $file->size;
        } else {
            $totalSize += $file['size'];
        }
    }

    return formatBytes($totalSize);
}

function getFileListFormat($array)
{
    $totalHTML = "";

    for ($i = 0; $i < min(count($array), 8); $i++) {
        ob_start();
        include '../template/file.item.format.html';
        $body = ob_get_clean();

        if (is_object($array[$i])) {
            $fileName = $array[$i]->name;
            $fileSize = $array[$i]->size;
        } else {
            $fileName = $array[$i]['name'];
            $fileSize = $array[$i]['size'];
        }

        $parts = explode(".", $fileName);
        $extension = $parts[count($parts) - 1];

        $body = str_replace("{{FILE-NAME}}", $fileName, $body);
        $body = str_replace("{{SIZE}}", formatBytes($fileSize), $body);
        $body = str_replace("{{EXTENSIE}}", $extension, $body);
        $totalHTML .= $body;
    }
    return $totalHTML;
}

function doesTransferExist($transfer_id)
{
    require_once("../services/databaseQueries.php");

    $parameters = [
        0 => $transfer_id
    ];

    $sql = "SELECT COUNT(*) FROM `shared_files` WHERE transfer_id = ?";

    $count = databaseActions($sql, $parameters);

    return $count[0]["COUNT(*)"] >= 1;
}

function deleteDirectory($dir)
{
    if (!is_dir($dir)) {
        return false;
    }

    $items = array_diff(scandir($dir), ['.', '..']);

    foreach ($items as $item) {
        $itemPath = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($itemPath)) {
            deleteDirectory($itemPath);
        } else {
            unlink($itemPath);
        }
    }

    return rmdir($dir);
}

function ExtractSpecificFile($fileName, $zipFilePath, $outputDir)
{
    $zip = new ZipArchive;
    if ($zip->open($zipFilePath) === TRUE) {
        $zip->extractTo($outputDir, $fileName);
        $zip->close();
        return $outputDir . DIRECTORY_SEPARATOR . $fileName;
    } else {
        throw new Exception("Zip-bestand openen mislukt");
    }
}

function downloadFileToUser($filePath)
{

    if ($filePath && file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
    } else {
        throw new Exception("File does not exist or is not accessible: " . $filePath);
    }
}

function DatabaseDecrypting($transfer_id)
{

    $sql = "SELECT * FROM `shared_files` WHERE transfer_id = ?";

    $parameters = [
        0 => $transfer_id
    ];

    $response = databaseActions($sql, $parameters);

    $encr_iv = $response[0]['decrypt_iv'];
    $encr_key = $response[0]['decrypt_key'];

    if ($encr_iv === null || $encr_key === null)
        return [
            "key" => null,
            "iv" => null,
        ];

    return decryptTheDecryptKeys($encr_key, $encr_iv);
}

function returnProperExpireDate($date)
{
    $parts = explode('-', $date);

    $monthMap = [
        "Januari",
        "Februari",
        "Maart",
        "April",
        "Mei",
        "Juni",
        "Juli",
        "Augstus",
        "September",
        "Oktober",
        "November",
        "December"
    ];

    $year = $parts[0];
    $month = $monthMap[$parts[1] - 1];
    $day = ($parts[2] < 10) ? str_replace(0, "", $parts[2]) : $parts[2];

    return "$day $month $year";
}
