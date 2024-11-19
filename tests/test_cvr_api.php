<?php
// tests/test_cvr_api.php

require_once __DIR__ . '/../app/company.php';

$manager = new CompanyManager();

// Test sync for company with ID 5 (ÅRHUS ApS)
echo "Testing sync for ÅRHUS ApS (ID: 5)\n";
$result = $manager->syncCompany(5);
var_dump($result);

// Verify the update
if ($result['success']) {
    $company = $manager->getCompany(5);
    echo "\nUpdated company data:\n";
    var_dump($company);
}