<?php

/**
 * Fetch Book Data
 * 
 * This script fetches book data from the Open Library API using the provided ISBN.
 * It returns the book title, authors, publisher, publish date, and URL in JSON format.
 */

// Include the configuration file
try {
    require 'config.php';
} catch (\Throwable $th) {
    die('config.php file not found. Have you renamed from config_dummy.php?');
}

// Read the CSV file
$data = [];
if (($handle = fopen(CSVFILE, "r")) !== false) {
    while (($row = fgetcsv($handle, 1000, ",", escape: "")) !== false) {
        $data[] = $row;
    }
    fclose($handle);
}

$output = '';
for ($i = 0; $i < count($data); $i++) {
    $output .= "<tr>";
    for ($j = 0; $j < 3; $j++) {
        if ($j == 0) {
            $output .= "<td><a href='" . htmlspecialchars($data[$i][$j + 3]) . "' target='_blank'>" . htmlspecialchars($data[$i][$j]) . "</a></td>";
        } else {
            // Display the author and genre without links
            if ($j == 1) {
                $output .= "<td>" . htmlspecialchars($data[$i][$j]) . "</td>";
            } else {
                $output .= "<td>" . htmlspecialchars($data[$i][$j]) . "</td>";
            }
        }
    }
    $output .= "</tr>";
}

echo $output;
