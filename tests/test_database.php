<?php
// db/test_database.php

try {
    // Connect to the database
    $db = new PDO('sqlite:' . __DIR__ . '/../data/companies.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test query
    $stmt = $db->query('SELECT * FROM companies');
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Database connection successful!\n";
    echo "Found " . count($companies) . " companies:\n";
    
    foreach($companies as $company) {
        echo "- {$company['name']} (CVR: {$company['cvr_number']})\n";
    }
    
} catch (PDOException $e) {
    die("Database test failed: " . $e->getMessage() . "\n");
}