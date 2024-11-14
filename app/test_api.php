<?php
// app/test_api.php
require_once 'api.php';

echo "=== Testing CVR API Integration ===\n\n";

$api = new CVRAPI();

// Test valid CVR number
$testCvr = '28856636'; // ÅRHUS ApS
echo "1. Testing valid CVR lookup ($testCvr):\n";
$result = $api->fetchCompanyData($testCvr);

if ($result['success']) {
    echo "✓ Company found:\n";
    echo "  - Name: {$result['data']['name']}\n";
    echo "  - Address: {$result['data']['address']}\n";
    echo "  - Phone: {$result['data']['phone']}\n";
    echo "  - Email: {$result['data']['email']}\n";
    echo "  - Industry: {$result['data']['industry']}\n";
} else {
    echo "✗ Error: {$result['error']}\n";
}

// Test invalid CVR number
echo "\n2. Testing invalid CVR format:\n";
$result = $api->fetchCompanyData('123');
echo $result['success'] === false ? 
    "✓ Correctly caught invalid format\n" : 
    "✗ Failed to catch invalid format\n";

// Test company validation
echo "\n3. Testing company validation:\n";
$isValid = $api->validateCompany($testCvr);
echo $isValid ? 
    "✓ Company validation successful\n" : 
    "✗ Company validation failed\n";

echo "\nTest completed!\n";