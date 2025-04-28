<?php

/**
 * Fetch Book Data
 * 
 * This script fetches book data from the Open Library API using the provided ISBN.
 * It returns the book title, authors, publisher, publish date, and URL in JSON format.
 */

// Set the content type to JSON
header('Content-Type: application/json');

if (!isset($_GET['isbn'])) {
    echo json_encode(['error' => 'ISBN not provided']);
    exit;
}

$isbn = $_GET['isbn'];
$url = "https://openlibrary.org/api/books?bibkeys=ISBN:$isbn&format=json&jscmd=data";

$response = file_get_contents($url);
if ($response === FALSE) {
    echo json_encode(['error' => 'Unable to fetch data from Open Library API']);
    exit;
}

$data = json_decode($response, true);
if (isset($data["ISBN:$isbn"])) {
    $bookData = $data["ISBN:$isbn"];
    echo json_encode([
        'title' => $bookData['title'],
        'authors' => array_map(function ($author) {
            return $author['name'];
        }, $bookData['authors']),
        'publisher' => $bookData['publishers'][0]['name'],
        'publish_date' => $bookData['publish_date'],
        'url' => $bookData['url'],
        'subject' => $bookData['subjects'][0]['name'] ?: 'Unknown',
        'isbn' => $isbn,
        'cover' => $bookData['cover']['large'] ?? null,
    ]);
} else {
    echo json_encode(['error' => 'No book found with the provided ISBN']);
}
