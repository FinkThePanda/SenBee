<?php
// db/create_database.php

// Set the correct path to data directory
$dbPath = __DIR__ . '/../data/companies.db';
$schemaPath = __DIR__ . '/schema.sql';

try {
    // Check if data directory exists, if not create it
    $dataDir = dirname($dbPath);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0777, true);
    }
    
    // Create SQLite database
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute SQL schema
    $sql = file_get_contents($schemaPath);
    $db->exec($sql);
    
    echo "Database created successfully at: $dbPath\n";
    echo "Schema applied from: $schemaPath\n";
} catch (PDOException $e) {
    die("Database creation failed: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}