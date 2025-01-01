<?php 

function returnFormattedDate() {
    setlocale(LC_TIME, 'nl_NL.UTF-8');
    $date = new DateTime();
    $date->modify('+2 weeks');
    $formattedDate = $date->format('l d F');

    $days = [
        'Monday' => 'maandag',
        'Tuesday' => 'dinsdag',
        'Wednesday' => 'woensdag',
        'Thursday' => 'donderdag',
        'Friday' => 'vrijdag',
        'Saturday' => 'zaterdag',
        'Sunday' => 'zondag'
    ];

    $months = [
        'January' => 'januari',
        'February' => 'februari',
        'March' => 'maart',
        'April' => 'april',
        'May' => 'mei',
        'June' => 'juni',
        'July' => 'juli',
        'August' => 'augustus',
        'September' => 'september',
        'October' => 'oktober',
        'November' => 'november',
        'December' => 'december'
    ];

    $formattedDate = str_replace(array_keys($days), array_values($days), $formattedDate);
    $formattedDate = str_replace(array_keys($months), array_values($months), $formattedDate);

    return $formattedDate;
}


?>