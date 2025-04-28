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

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate the received data
    if (isset($data['title'], $data['authors'], $data['url'])) {
        $title = $data['title'];
        $authors = $data['authors'];
        $url = $data['url'];
        $subject = $data['subject'];

        // Define the file path
        $filePath = CSVFILE;

        // Open the file in append mode
        if ($file = fopen($filePath, 'a')) {
            // Write the data to the file
            fputcsv($file, [$title, $authors, $subject, $url]);
            fclose($file);

            // Return a success response
            echo json_encode(['success' => true]);
        } else {
            // Return an error response if the file couldn't be opened
            echo json_encode(['success' => false, 'error' => 'Unable to open the file.']);
        }
    } else {
        // Return an error response if the data is invalid
        echo json_encode(['success' => false, 'error' => 'Invalid data received.']);
    }
} else {
    // Return an error response if the request method is not POST
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
