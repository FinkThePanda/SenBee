<?php
// app/company.php
require_once 'config.php';

class CompanyManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Create new company
    public function createCompany($cvr_number) {
        try {
            if (!preg_match('/^\d{8}$/', $cvr_number)) {
                throw new Exception('Invalid CVR number format');
            }

            $stmt = $this->db->prepare('SELECT id FROM companies WHERE cvr_number = ?');
            $stmt->execute([$cvr_number]);
            if ($stmt->fetch()) {
                throw new Exception('Company already exists');
            }

            // Insert basic company record
            $stmt = $this->db->prepare('
                INSERT INTO companies 
                (cvr_number, created_at, updated_at) 
                VALUES (?, datetime("now"), datetime("now"))
            ');
            $stmt->execute([$cvr_number]);
            
            $newId = $this->db->lastInsertId();

            // Immediately sync with CVR API
            $syncResult = $this->syncCompany($newId);
            
            if (!$syncResult['success']) {
                // Log the sync failure but don't treat it as a creation failure
                error_log("Failed to sync new company: " . ($syncResult['error'] ?? 'Unknown error'));
            }

            return [
                'success' => true,
                'message' => 'Company created successfully',
                'id' => $newId
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Get all companies
    public function getCompanies() {
        try {
            $stmt = $this->db->query('SELECT * FROM companies ORDER BY created_at DESC');
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Get single company
    public function getCompany($id) {
        try {
            $stmt = $this->db->prepare('SELECT * FROM companies WHERE id = ?');
            $stmt->execute([$id]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$company) {
                throw new Exception('Company not found');
            }

            return ['success' => true, 'data' => $company];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Delete company
    public function deleteCompany($id) {
        try {
            $stmt = $this->db->prepare('DELETE FROM companies WHERE id = ?');
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Company deleted'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Sync company
    public function syncCompany($id) {
        try {
            // Get company CVR
            $stmt = $this->db->prepare('SELECT cvr_number FROM companies WHERE id = ?');
            $stmt->execute([$id]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$company) {
                throw new Exception('Company not found');
            }

            // Call CVR API
            $url = sprintf(
                '%s?search=%s&country=%s',
                CVR_API_URL,
                urlencode($company['cvr_number']),
                CVR_COUNTRY
            );

            // Set context for API call
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: CompanyManager/1.0'
                    ]
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            
            if (!$response) {
                throw new Exception('Failed to fetch data from CVR API');
            }

            $data = json_decode($response, true);
            
            if (!$data) {
                throw new Exception('Invalid response from CVR API');
            }

            // Update company data
            $stmt = $this->db->prepare('
                UPDATE companies 
                SET 
                    name = ?, 
                    phone = ?, 
                    email = ?, 
                    address = ?,
                    last_synced = datetime("now"),
                    updated_at = datetime("now")
                WHERE id = ?
            ');
            
            $address = implode(', ', array_filter([
                $data['address'] ?? null,
                $data['zipcode'] ?? null,
                $data['city'] ?? null
            ]));

            $stmt->execute([
                $data['name'] ?? null,
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $address,
                $id
            ]);

            return ['success' => true, 'message' => 'Company synced successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}