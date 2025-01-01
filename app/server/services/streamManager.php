<?php 

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');


function sendSSE($message) {
    echo "data: " . json_encode($message) . "\n\n";
    flush();
}



?>