<?php
// tests/test_api.php

require_once __DIR__ . '/../app/company.php';

function testCompanyOperations() {
    $manager = new CompanyManager();
    echo "=== Testing Company Operations ===\n\n";

    // Test creating company
    $cvr = '28856636'; // ÅRHUS ApS
    echo "1. Creating company with CVR: $cvr\n";
    $result = $manager->createCompany($cvr);
    print_r($result);

    if ($result['success']) {
        // Test syncing
        echo "\n2. Syncing company data\n";
        $syncResult = $manager->syncCompany($result['id']);
        print_r($syncResult);

        // Get company details
        echo "\n3. Getting company details\n";
        $companyResult = $manager->getCompany($result['id']);
        print_r($companyResult);
    }
}

testCompanyOperations();