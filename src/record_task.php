<?php

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

        // RTM API Key and Shared Secret
        $apiKey = RTM_API_KEY;
        $sharedSecret = RTM_SHARED_SECRET;

        $taskResponse = createTask($title . ' - ' . $authors . ' ' . $url);
        if ($taskResponse['rsp']['stat'] == 'ok') {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => "Error creating task: " . $taskResponse['rsp']['err']['msg'] . ' (' . $taskResponse['rsp']['err']['code'] . ')']);
        }
    } else {
        // Return an error response if the data is invalid
        echo json_encode(['success' => false, 'error' => 'Invalid data received.']);
    }
} else {
    // Return an error response if the request method is not POST
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}

// Function to generate an API signature
function generateSignature($params, $sharedSecret)
{
    ksort($params);
    $sig = '';
    foreach ($params as $key => $value) {
        $sig .= $key . $value;
    }
    return md5($sharedSecret . $sig);
}

// Function to call RTM API
function callRTMApi($method, $params, $apiKey, $sharedSecret)
{
    $baseUrl = 'https://api.rememberthemilk.com/services/rest/';
    $params['method'] = $method;
    $params['api_key'] = $apiKey;
    $params['format'] = 'json';
    $params['perms'] = 'write';
    $params['auth_token'] = RTM_TOKEN;

    // Generate a timeline value
    $params['timeline'] = time(); // Use the current timestamp as the timeline

    // Generate API signature
    $params['api_sig'] = generateSignature($params, $sharedSecret);

    // Build the API URL
    $url = $baseUrl . '?' . http_build_query($params);

    // Make the API request
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// Function to create a task in RTM
function createTask($title, $listName = '📖 Reading')
{
    global $apiKey, $sharedSecret;

    $listId = RTM_LIST;

    // Step 2: Create the task
    $taskParams = [
        'name' => $title,
        'list_id' => $listId,
        'parse' => 1 // Automatically parse the task title
    ];
    return callRTMApi('rtm.tasks.add', $taskParams, $apiKey, $sharedSecret);
}
