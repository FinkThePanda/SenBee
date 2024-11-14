<?php
// app/test_config.php

require_once 'config.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Database connection successful!\n";
    
    $stmt = $db->query('SELECT COUNT(*) FROM companies');
    $count = $stmt->fetchColumn();
    echo "Found $count companies in database.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}