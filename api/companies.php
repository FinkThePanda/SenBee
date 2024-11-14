<?php
// api/companies.php

header('Content-Type: application/json');
require_once '../app/config.php';
require_once '../app/company.php';

try {
    $companyManager = new CompanyManager();
    
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get all companies or single company
            if (isset($_GET['id'])) {
                $result = $companyManager->getCompany($_GET['id']);
            } else {
                $result = $companyManager->getCompanies();
            }
            break;

        case 'POST':
            // Add new company
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['cvr_number'])) {
                throw new Exception('CVR number is required');
            }
            $result = $companyManager->createCompany($data['cvr_number']);
            break;

        case 'DELETE':
            // Delete company
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
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}