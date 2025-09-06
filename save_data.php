<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get the posted JSON data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Validate the data structure
if (!isset($data['players']) || !isset($data['matches'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required data fields']);
    exit;
}

// Ensure the data directory exists
$dataDir = __DIR__;
if (!is_writable($dataDir)) {
    http_response_code(500);
    echo json_encode(['error' => 'Data directory is not writable']);
    exit;
}

// File path for the JSON data
$dataFile = $dataDir . '/data.json';

// Add timestamp if not provided
if (!isset($data['lastUpdated'])) {
    $data['lastUpdated'] = time() * 1000; // JavaScript timestamp format
}

// Try to save the data
try {
    $jsonString = json_encode($data, JSON_PRETTY_PRINT);
    
    if ($jsonString === false) {
        throw new Exception('Failed to encode data as JSON');
    }
    
    $result = file_put_contents($dataFile, $jsonString, LOCK_EX);
    
    if ($result === false) {
        throw new Exception('Failed to write data to file');
    }
    
    // Set appropriate permissions
    chmod($dataFile, 0644);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Data saved successfully',
        'timestamp' => $data['lastUpdated']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to save data: ' . $e->getMessage()
    ]);
}
?>