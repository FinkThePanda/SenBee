<?php
// app/test_integration.php
require_once 'company.php';

echo "=== Testing Company Manager with API Integration ===\n\n";

$manager = new CompanyManager();

// Test creating a company with API data
echo "1. Testing company creation with API data:\n";
$createResult = $manager->createCompany('28856636');
if ($createResult['success']) {
    echo "✓ Company created" . 
         (isset($createResult['source']) ? " (using {$createResult['source']} data)" : "") . 
         "\n";
    
    // Test syncing the company
    echo "\n2. Testing company sync:\n";
    $syncResult = $manager->syncCompany($createResult['id']);
    echo $syncResult['success'] ? 
        "✓ Company synced successfully\n" : 
        "✗ Sync failed: {$syncResult['error']}\n";
    
    // Verify the data
    $company = $manager->getCompany($createResult['id']);
    if ($company['success']) {
        echo "\n3. Verified company data:\n";
        echo "  - Name: {$company['data']['name']}\n";
        echo "  - Phone: {$company['data']['phone']}\n";
        echo "  - Email: {$company['data']['email']}\n";
        echo "  - Address: {$company['data']['address']}\n";
    }
} else {
    echo "✗ Creation failed: {$createResult['error']}\n";
}

echo "\nTest completed!\n";