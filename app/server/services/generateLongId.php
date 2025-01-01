<?php 

function GenerateIdentifierLong($length) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charactersLength = strlen($characters);
    $result = [];

    for ($i = 0; $i < $length; $i++) {
        $segment = '';
        for ($j = 0; $j < 10; $j++) { 
            $segment .= $characters[rand(0, $charactersLength - 1)];
        }
        $result[] = $segment;
    }

    return implode('-', $result);
}

?>