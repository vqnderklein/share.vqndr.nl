<?php 

function databaseActions($SQL, $parameters) {
    global $link;
    require_once("../config.php"); // Ensure $link is defined

    $stmt = $link->prepare($SQL);

    if ($stmt === false) {
        die("Error preparing statement: " . $link->error);
    }

    $types = '';
    foreach ($parameters as $param) {
        if (is_int($param)) {
            $types .= 'i';
        } elseif (is_float($param)) {
            $types .= 'd';
        } elseif (is_string($param)) {
            $types .= 's';
        } else {
            $types .= 'b';
        }
    }

    if (!empty($parameters)) {
        $stmt->bind_param($types, ...$parameters);
    }

    $stmt->execute();

    if (stripos($SQL, 'SELECT') === 0) {
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    } else {
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows;
    }
}



?>