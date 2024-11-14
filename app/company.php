<?php
// app/company.php
require_once 'config.php';

class CompanyManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // CREATE
    public function createCompany($cvr_number) {
        try {
            // Validate CVR number
            if (!preg_match('/^\d{8}$/', $cvr_number)) {
                throw new Exception('Invalid CVR number format');
            }

            // Check if company exists
            $stmt = $this->db->prepare('SELECT id FROM companies WHERE cvr_number = ?');
            $stmt->execute([$cvr_number]);
            if ($stmt->fetch()) {
                throw new Exception('Company already exists');
            }

            // Insert new company
            $stmt = $this->db->prepare('
                INSERT INTO companies (cvr_number) 
                VALUES (?)
            ');
            $stmt->execute([$cvr_number]);
            
            return [
                'success' => true, 
                'message' => 'Company created',
                'id' => $this->db->lastInsertId()
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // READ (single company)
    public function getCompany($id) {
        try {
            $stmt = $this->db->prepare('
                SELECT * FROM companies 
                WHERE id = ?
            ');
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

    // READ (all companies)
    public function getCompanies() {
        try {
            $stmt = $this->db->query('
                SELECT * FROM companies 
                ORDER BY datetime(created_at) DESC
            ');
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    // UPDATE
    public function updateCompany($id, $data) {
        try {
            $stmt = $this->db->prepare('SELECT id FROM companies WHERE id = ?');
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                throw new Exception('Company not found');
            }

            $stmt = $this->db->prepare('
                UPDATE companies 
                SET name = ?, 
                    phone = ?, 
                    email = ?, 
                    address = ?, 
                    updated_at = datetime("now")
                WHERE id = ?
            ');
            
            $stmt->execute([
                $data['name'] ?? null,
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $data['address'] ?? null,
                $id
            ]);

            return ['success' => true, 'message' => 'Company updated'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    // DELETE
    public function deleteCompany($id) {
        try {
            // Check if company exists
            $stmt = $this->db->prepare('SELECT id FROM companies WHERE id = ?');
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                throw new Exception('Company not found');
            }

            // Delete company
            $stmt = $this->db->prepare('DELETE FROM companies WHERE id = ?');
            $stmt->execute([$id]);
            
            return ['success' => true, 'message' => 'Company deleted'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // SYNC (additional operation)
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
            $url = CVR_API_URL . '?search=' . urlencode($company['cvr_number']) . '&country=' . CVR_COUNTRY;
            $response = @file_get_contents($url);
            
            if (!$response) {
                throw new Exception('API call failed');
            }

            $data = json_decode($response, true);
            
            // Update company using the UPDATE method
            return $this->updateCompany($id, [
                'name' => $data['name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null
            ]);
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}