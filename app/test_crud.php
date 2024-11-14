<?php
// app/test_crud.php
require_once 'company.php';

$manager = new CompanyManager();

echo "=== CRUD Operations Test ===\n\n";

// CREATE
echo "1. Testing CREATE:\n";
$createResult = $manager->createCompany('12345678');
echo $createResult['success'] ? 
    "✓ Company created with ID: {$createResult['id']}\n" : 
    "✗ Error: {$createResult['error']}\n";

if ($createResult['success']) {
    $companyId = $createResult['id'];
    
    // READ (single)
    echo "\n2. Testing READ (single):\n";
    $readResult = $manager->getCompany($companyId);
    echo $readResult['success'] ? 
        "✓ Found company: {$readResult['data']['cvr_number']}\n" : 
        "✗ Error: {$readResult['error']}\n";

    // READ (all)
    echo "\n3. Testing READ (all):\n";
    $readAllResult = $manager->getCompanies();
    echo $readAllResult['success'] ? 
        "✓ Found " . count($readAllResult['data']) . " companies\n" : 
        "✗ Error: {$readAllResult['error']}\n";

    // UPDATE
    echo "\n4. Testing UPDATE:\n";
    $updateResult = $manager->updateCompany($companyId, [
        'name' => 'Test Company',
        'phone' => '12345678',
        'email' => 'test@example.com',
        'address' => 'Test Address'
    ]);
    echo $updateResult['success'] ? 
        "✓ Company updated\n" : 
        "✗ Error: {$updateResult['error']}\n";

    // Verify UPDATE
    $verifyUpdate = $manager->getCompany($companyId);
    if ($verifyUpdate['success']) {
        echo "  Verified new data:\n";
        echo "  - Name: {$verifyUpdate['data']['name']}\n";
        echo "  - Phone: {$verifyUpdate['data']['phone']}\n";
        echo "  - Email: {$verifyUpdate['data']['email']}\n";
    }

    // DELETE
    echo "\n5. Testing DELETE:\n";
    $deleteResult = $manager->deleteCompany($companyId);
    echo $deleteResult['success'] ? 
        "✓ Company deleted\n" : 
        "✗ Error: {$deleteResult['error']}\n";

    // Verify DELETE
    $verifyDelete = $manager->getCompany($companyId);
    echo $verifyDelete['success'] === false ? 
        "✓ Verified deletion (company not found)\n" : 
        "✗ Company still exists\n";
}

echo "\nTest completed!\n";