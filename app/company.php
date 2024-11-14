<?php
// app/company.php
require_once 'config.php';
require_once 'api.php';

class CompanyManager {
    private $db;
    private $api;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->api = new CVRAPI();
    }

    // CREATE - with API validation
    public function createCompany($cvr_number) {
        try {
            // Validate CVR number format
            if (!preg_match('/^\d{8}$/', $cvr_number)) {
                throw new Exception('Invalid CVR number format');
            }

            // Check if company exists in database
            $stmt = $this->db->prepare('SELECT id FROM companies WHERE cvr_number = ?');
            $stmt->execute([$cvr_number]);
            if ($stmt->fetch()) {
                throw new Exception('Company already exists in database');
            }

            // Validate company exists in CVR register
            $apiData = $this->api->fetchCompanyData($cvr_number);
            if (!$apiData['success']) {
                throw new Exception('Could not verify company in CVR register');
            }

            // Insert company with initial data from API
            $stmt = $this->db->prepare('
                INSERT INTO companies 
                (cvr_number, name, phone, email, address, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, datetime("now"), datetime("now"))
            ');
            
            $stmt->execute([
                $cvr_number,
                $apiData['data']['name'],
                $apiData['data']['phone'],
                $apiData['data']['email'],
                $apiData['data']['address']
            ]);
            
            $id = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Company created',
                'id' => $id,
                'source' => $apiData['source'] ?? 'database'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // SYNC - updated to use new API class
    public function syncCompany($id) {
        try {
            // Get company CVR
            $stmt = $this->db->prepare('SELECT cvr_number FROM companies WHERE id = ?');
            $stmt->execute([$id]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$company) {
                throw new Exception('Company not found');
            }

            // Get data from API
            $apiData = $this->api->fetchCompanyData($company['cvr_number']);
            if (!$apiData['success']) {
                throw new Exception('Failed to fetch company data: ' . ($apiData['error'] ?? 'Unknown error'));
            }

            // Update company data
            return $this->updateCompany($id, [
                'name' => $apiData['data']['name'],
                'phone' => $apiData['data']['phone'],
                'email' => $apiData['data']['email'],
                'address' => $apiData['data']['address']
            ]);

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Other methods remain the same...
}