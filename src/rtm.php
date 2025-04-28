<?php

/**
 * Remember The Milk API Authentication Script
 *
 * This script handles the OAuth authentication process for Remember The Milk API.
 * It retrieves a frob, redirects the user to authorize the app, and then retrieves
 * the token to be used for API calls.
 *
 * Make sure to replace 'your_api_key' and 'your_shared_secret' with your actual
 * Remember The Milk API credentials in config.php.
 */

// Include the configuration file
try {
    require 'config.php';
} catch (\Throwable $th) {
    die('config.php file not found. Have you renamed from config_dummy.php?');
}

// Step 1: Get a frob (request token equivalent)
if (!isset($_GET['frob'])) {
    $authUrl = "https://www.rememberthemilk.com/services/auth/";
    $params = [
        'api_key' => RTM_API_KEY,
        'perms' => 'write',
        'format' => 'json',
    ];

    // Generate the API signature
    ksort($params);
    $sig = RTM_SHARED_SECRET;
    foreach ($params as $key => $value) {
        $sig .= $key . $value;
    }
    $params['api_sig'] = md5($sig);

    // Redirect user to Remember The Milk for authorization
    $queryString = http_build_query($params);
    header("Location: $authUrl?$queryString");
    exit;
}

// Step 2: Handle the callback and get the token
if (isset($_GET['frob'])) {
    $frob = $_GET['frob'];

    // Generate the API signature for token retrieval
    $params = [
        'api_key' => RTM_API_KEY,
        'method' => 'rtm.auth.getToken',
        'frob' => $frob,
        'format' => 'json',
    ];

    ksort($params);
    $sig = RTM_SHARED_SECRET;
    foreach ($params as $key => $value) {
        $sig .= $key . $value;
    }
    $params['api_sig'] = md5($sig);

    // Make the API call to get the token
    $url = "https://api.rememberthemilk.com/services/rest/?" . http_build_query($params);
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data['rsp']['auth']['token'])) {
        $token = $data['rsp']['auth']['token'];

        // Save the token to config.php
        $configContent = "<?php\n";
        $configContent .= "define('RTM_API_KEY', '" . RTM_API_KEY . "');\n";
        $configContent .= "define('RTM_SHARED_SECRET', '" . RTM_SHARED_SECRET . "');\n";
        $configContent .= "define('RTM_TOKEN', '" . $token . "');\n";

        file_put_contents('config.php', $configContent);
        echo "Authorization successful! Token saved to config.php.";
    } else {
        echo "Failed to retrieve token.";
    }
}
