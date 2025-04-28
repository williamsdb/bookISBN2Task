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
    while (($row = fgetcsv($handle, 1000, ",")) !== false) {
        $data[] = $row;
    }
    fclose($handle);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Include jQuery and DataTables CSS/JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <link rel="apple-touch-icon" href="./apple-touch-icon.png" />
    <link rel="icon" href="./apple-touch-icon.png" />
    <title>Barcode Scanner</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }

        h1 {
            font-size: 24px;
        }

        button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        #camera {
            width: 100vw;
            /* Full width */
            height: 56vw;
            /* Maintain 16:9 aspect ratio (wider than tall) */
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
            /* Fill the container */
            border: 1px solid #007bff;
        }

        #icon {
            position: relative;
            top: 7px;
            right: 7px;
            width: 30px;
        }

        #scan-button {
            display: none;
            margin: 20px auto;
        }

        #read-button {
            display: none;
            margin: 20px auto;
        }
    </style>
</head>

<body>
    <h1><img src="./apple-touch-icon.png" id="icon" />Scan ISBN Barcode</h1>
    <h3>
        <a href="/" style="text-decoration: none">🏠</a>
        <a href="search.html" style="text-decoration: none">🔍</a>
        <a href="booklist.php" style="text-decoration: none">📚</a>
        <a href="https://spokenlikeageek.com" style="text-decoration: none">❓</a>
    </h3>

    <table id="csvTable" class="display">
        <thead>
            <tr>
                <td>Title</td>
                <td>Author</td>
                <td>Genre</td>
            </tr>
        </thead>
        <tbody>
            <?php
            for ($i = 0; $i < count($data); $i++) {
                echo "<tr>";
                for ($j = 0; $j < 3; $j++) {
                    if ($j == 0) {
                        echo "<td><a href='" . htmlspecialchars($data[$i][$j + 3]) . "' target='_blank'>" . htmlspecialchars($data[$i][$j]) . "</a></td>";
                    } else {
                        // Display the author and genre without links
                        if ($j == 1) {
                            echo "<td>" . htmlspecialchars($data[$i][$j]) . "</td>";
                        } else {
                            echo "<td>" . htmlspecialchars($data[$i][$j]) . "</td>";
                        }
                    }
                }
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            $('#csvTable').DataTable();
        });
    </script>
    <small>Built by
        <a href="https://neilthompson.me" target="_blank">Neil Thompson</a>.</small>
</body>

</html>