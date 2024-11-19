<?php
// app/company.php

require_once 'config.php';

class CompanyManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getCompanies() {
        try {
            $stmt = $this->db->query('SELECT * FROM companies ORDER BY created_at DESC');
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

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

            $stmt = $this->db->prepare('
                INSERT INTO companies (cvr_number, created_at, updated_at) 
                VALUES (?, datetime("now"), datetime("now"))
            ');
            $stmt->execute([$cvr_number]);
            
            return [
                'success' => true,
                'message' => 'Company created successfully',
                'id' => $this->db->lastInsertId()
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function syncCompany($id) {
        try {
            $stmt = $this->db->prepare('SELECT cvr_number FROM companies WHERE id = ?');
            $stmt->execute([$id]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$company) {
                throw new Exception('Company not found');
            }
    
            $ch = curl_init();
            $url = 'https://cvrapi.dk/api?search=' . urlencode($company['cvr_number']) . '&country=dk';
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => 'Company Manager - Educational Project',
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,  // Disable SSL verification
                CURLOPT_SSL_VERIFYHOST => 0       // Disable host verification
            ]);
    
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
    
            if ($error) {
                throw new Exception('cURL Error: ' . $error);
            }
    
            if (!$response) {
                throw new Exception('Empty response from API');
            }
    
            $data = json_decode($response, true);
            if (!$data) {
                throw new Exception('Invalid JSON response');
            }
    
            $stmt = $this->db->prepare('
                UPDATE companies 
                SET name = ?, 
                    phone = ?, 
                    email = ?, 
                    address = ?,
                    last_synced = datetime("now")
                WHERE id = ?
            ');
    
            $address = implode(', ', array_filter([
                $data['address'] ?? '',
                $data['zipcode'] ?? '',
                $data['city'] ?? ''
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

    public function deleteCompany($id) {
        try {
            $stmt = $this->db->prepare('DELETE FROM companies WHERE id = ?');
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Company deleted successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}