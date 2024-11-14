<?php
// app/test_config.php

echo "=== Comprehensive Configuration and API Test Suite ===\n\n";

echo "1. Database Tests:\n";
require_once 'config.php';
try {
    $db = Database::getInstance()->getConnection();
    echo "✓ Database connection successful!\n";
    
    // Test database structure
    echo "\n  Testing database structure:\n";
    $tables = ['companies', 'sync_history'];
    foreach ($tables as $table) {
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
        echo "  " . ($stmt->fetch() ? "✓" : "✗") . " Table '$table' exists\n";
    }

    // Test required columns in companies table
    echo "\n  Testing companies table structure:\n";
    $stmt = $db->query("PRAGMA table_info(companies)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    $requiredColumns = ['id', 'cvr_number', 'name', 'phone', 'email', 'address', 'created_at', 'updated_at'];
    foreach ($requiredColumns as $column) {
        echo "  " . (in_array($column, $columns) ? "✓" : "✗") . " Column '$column' exists\n";
    }

    // Test data integrity
    echo "\n  Testing data integrity:\n";
    $stmt = $db->query('SELECT COUNT(*) FROM companies WHERE cvr_number IS NULL');
    $invalidRecords = $stmt->fetchColumn();
    echo "  " . ($invalidRecords === 0 ? "✓" : "✗") . " All companies have CVR numbers\n";
    
    $stmt = $db->query('SELECT COUNT(DISTINCT cvr_number) = COUNT(*) FROM companies');
    echo "  " . ($stmt->fetchColumn() ? "✓" : "✗") . " All CVR numbers are unique\n";

} catch (Exception $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
}

echo "\n2. API Integration Tests:\n";
require_once 'api.php';
try {
    $api = new CVRAPI();
    
    // Basic API functionality
    $testCvr = '28856636';
    echo "  Testing CVR lookup ($testCvr):\n";
    $result = $api->fetchCompanyData($testCvr);
    if ($result['success']) {
        echo "  ✓ Company found" . ($result['source'] === 'mock' ? " (using mock data)" : "") . "\n";
        
        // Test data completeness
        $requiredFields = ['name', 'address', 'phone', 'email'];
        foreach ($requiredFields as $field) {
            echo "    " . (isset($result['data'][$field]) ? "✓" : "✗") . " Has '$field' field\n";
        }
    }

    // Error handling tests
    echo "\n  Testing error handling:\n";
    
    // Invalid format
    $result = $api->fetchCompanyData('123');
    echo "  " . ($result['success'] === false ? "✓" : "✗") . " Catches invalid CVR format\n";
    
    // Empty input
    $result = $api->fetchCompanyData('');
    echo "  " . ($result['success'] === false ? "✓" : "✗") . " Handles empty input\n";
    
    // Non-numeric input
    $result = $api->fetchCompanyData('abcdefgh');
    echo "  " . ($result['success'] === false ? "✓" : "✗") . " Catches non-numeric input\n";

    // Mock data tests
    echo "\n  Testing mock data system:\n";
    $mockCvrs = array_keys((new ReflectionClass('CVRAPI'))->getProperty('mockData')->getValue(new CVRAPI()));
    echo "  ✓ " . count($mockCvrs) . " mock companies available\n";
    foreach ($mockCvrs as $cvr) {
        $result = $api->fetchCompanyData($cvr);
        echo "  " . ($result['success'] ? "✓" : "✗") . " Mock data works for CVR: $cvr\n";
    }

    // Performance test
    echo "\n  Testing API performance:\n";
    $start = microtime(true);
    $api->fetchCompanyData($testCvr);
    $time = (microtime(true) - $start) * 1000;
    echo "  ✓ Response time: " . round($time, 2) . "ms\n";

} catch (Exception $e) {
    echo "✗ API Error: " . $e->getMessage() . "\n";
}

echo "\n3. Integration Tests:\n";
try {
    // Test database updates after API calls
    $db = Database::getInstance()->getConnection();
    
    // Insert test
    echo "  Testing company creation:\n";
    $stmt = $db->prepare('INSERT INTO companies (cvr_number) VALUES (?) RETURNING id');
    $stmt->execute(['12345678']);
    $id = $stmt->fetchColumn();
    echo "  ✓ Test company created\n";
    
    // Update test
    $stmt = $db->prepare('UPDATE companies SET name = ? WHERE id = ?');
    $stmt->execute(['Test Company', $id]);
    echo "  ✓ Company data updated\n";
    
    // Cleanup
    $stmt = $db->prepare('DELETE FROM companies WHERE id = ?');
    $stmt->execute([$id]);
    echo "  ✓ Test data cleaned up\n";

} catch (Exception $e) {
    echo "✗ Integration Error: " . $e->getMessage() . "\n";
}

echo "\nTest Suite Completed!\n";