<?php
// tests/test_db.php

require_once __DIR__ . '/../app/config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get latest company
    $query = "SELECT * FROM companies ORDER BY created_at DESC LIMIT 1";
    $stmt = $db->query($query);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Latest Company:\n";
    print_r($company);

    // Get all companies
    echo "\nAll Companies:\n";
    $stmt = $db->query("SELECT * FROM companies");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
        echo "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}