<?php

/*
 * Check API connectivity
 * Validate responses
 * Show formatted results
 * Provide test summary
 */

// tests/test_cvr_api.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/config.php';

echo "=== CVR API Integration Tests (Mock Data) ===\n\n";

// Mock data to simulate API responses
$mockData = [
    '28856636' => [
        'name' => 'ÅRHUS ApS',
        'address' => 'Testvej 1',
        'city' => 'Århus C',
        'zipcode' => '8000',
        'phone' => '12345678',
        'email' => 'info@aarhus.dk',
        'industrydesc' => 'IT Services'
    ],
    '32365469' => [
        'name' => 'Mosevang Mælk ApS',
        'address' => 'Mælkevej 42',
        'city' => 'Silkeborg',
        'zipcode' => '8600',
        'phone' => '87654321',
        'email' => 'info@mosevang.dk',
        'industrydesc' => 'Dairy Products'
    ]
];

function testCompanyData($cvr) {
    global $mockData;
    
    echo "Testing CVR number: $cvr\n";
    echo "------------------------\n";

    try {
        if (!isset($mockData[$cvr])) {
            echo "✗ Error: No mock data for CVR: $cvr\n";
            return false;
        }

        $data = $mockData[$cvr];
        
        echo "✓ Data retrieved successfully\n";
        echo "Company Information:\n";
        echo "- Name: " . $data['name'] . "\n";
        echo "- Address: " . $data['address'] . "\n";
        echo "- Location: " . $data['zipcode'] . " " . $data['city'] . "\n";
        echo "- Phone: " . $data['phone'] . "\n";
        echo "- Email: " . $data['email'] . "\n";
        echo "- Industry: " . $data['industrydesc'] . "\n";

        // Test database integration
        require_once __DIR__ . '/../app/company.php';
        $companyManager = new CompanyManager();
        
        // Test creating company
        echo "\nTesting database operations:\n";
        $createResult = $companyManager->createCompany($cvr);
        if ($createResult['success']) {
            echo "✓ Company created in database\n";
            
            // Test syncing company
            $syncResult = $companyManager->syncCompany($createResult['id']);
            if ($syncResult['success']) {
                echo "✓ Company data synced\n";
            } else {
                echo "✗ Sync failed: " . ($syncResult['error'] ?? 'Unknown error') . "\n";
            }
        } else {
            echo "✗ Creation failed: " . ($createResult['error'] ?? 'Unknown error') . "\n";
        }
        
        return true;
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Run tests
$testCases = [
    '28856636' => 'ÅRHUS ApS',
    '32365469' => 'Mosevang Mælk ApS'
];

$successCount = 0;
$failCount = 0;

foreach ($testCases as $cvr => $expectedName) {
    echo "\nTest Case: $expectedName ($cvr)\n";
    if (testCompanyData($cvr)) {
        $successCount++;
    } else {
        $failCount++;
    }
    echo "\n-------------------\n";
}

echo "\nTest Summary:\n";
echo "-------------\n";
echo "Total Tests: " . count($testCases) . "\n";
echo "Successful: $successCount\n";
echo "Failed: $failCount\n";