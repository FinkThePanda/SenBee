<?php
// api/sync.php

header('Content-Type: application/json');
require_once '../app/config.php';
require_once '../app/company.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    if (!isset($_GET['id'])) {
        throw new Exception('Company ID is required');
    }

    $companyManager = new CompanyManager();
    
    // Log sync attempt
    error_log("Attempting to sync company ID: " . $_GET['id']);
    
    $result = $companyManager->syncCompany($_GET['id']);
    
    // Log sync result
    error_log("Sync result: " . json_encode($result));
    
    echo json_encode($result);

} catch (Exception $e) {
    error_log("Sync Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}