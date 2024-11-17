<?php
// api/companies.php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once '../app/config.php';
require_once '../app/company.php';

try {
    $companyManager = new CompanyManager();
    
    // Log the request method and data
    file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - Method: ' . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);

    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $result = $companyManager->getCompanies();
            break;

        case 'POST':
            $input = file_get_contents('php://input');
            file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - Input: ' . $input . "\n", FILE_APPEND);
            
            $data = json_decode($input, true);
            if (!isset($data['cvr_number'])) {
                throw new Exception('CVR number is required');
            }
            $result = $companyManager->createCompany($data['cvr_number']);
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                throw new Exception('Company ID is required');
            }
            $result = $companyManager->deleteCompany($_GET['id']);
            break;

        default:
            throw new Exception('Method not allowed');
    }

    echo json_encode($result);

} catch (Exception $e) {
    $error = [
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ];
    file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - Error: ' . json_encode($error) . "\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode($error);
}