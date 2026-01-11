<?php

function sendTextBeeSMS($number, $message)
{
    log_message('debug', 'sendTextBeeSMS called with: ' . $number . ' | ' . $message);
    
    // $apiKey   = '6a753f27-a11d-4d73-be05-2fafa6f59b76';  mine  // Your TextBee API Key
    // $deviceId = '695d17f8d6a8a5e2478416df';          // Your registered Device ID
    $apiKey   = 'facfdd06-c604-4297-af92-4c850c919056';    // Your TextBee API Key
    $deviceId = '69608dd9649a11e723902102';          // Your registered Device ID

    $url = "https://api.textbee.dev/api/v1/gateway/devices/$deviceId/send-sms";

    $data = [
        "recipients" => [$number],
        "message"    => $message
    ];

    log_message('debug', 'TextBee API Request: ' . $url);
    log_message('debug', 'TextBee Data: ' . json_encode($data));
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-api-key: $apiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For debugging only
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // For debugging only
    
    // Add verbose output for debugging
    if (ENVIRONMENT === 'development') {
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    if (ENVIRONMENT === 'development') {
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        log_message('debug', 'cURL Verbose: ' . $verboseLog);
        fclose($verbose);
    }
    
    curl_close($ch);
    
    log_message('debug', 'TextBee HTTP Code: ' . $httpCode);
    log_message('debug', 'TextBee Response: ' . $response);
    
    if ($error) {
        log_message('error', 'cURL Error: ' . $error);
        return json_encode([
            'status' => 'error',
            'message' => 'cURL Error: ' . $error,
            'http_code' => $httpCode
        ]);
    }
    
    // Parse response
    $responseData = json_decode($response, true);
    
    if (!$responseData) {
        log_message('error', 'Invalid JSON response from TextBee: ' . $response);
        return json_encode([
            'status' => 'error',
            'message' => 'Invalid JSON response from TextBee',
            'raw_response' => $response,
            'http_code' => $httpCode
        ]);
    }
    
    return $response;
}