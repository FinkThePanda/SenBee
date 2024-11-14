<?php
// api/sync.php

header('Content-Type: application/json');
require_once '../app/config.php';
require_once '../app/company.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    if (!isset($_GET['id'])) {
        throw new Exception('Company ID is required');
    }

    $companyManager = new CompanyManager();
    $result = $companyManager->syncCompany($_GET['id']);
    
    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}