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

            $stmt = $this->db->prepare('
                INSERT INTO companies (cvr_number, created_at, updated_at) 
                VALUES (?, datetime("now"), datetime("now"))
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
            $stmt = $this->db->prepare('SELECT cvr_number FROM companies WHERE id = ?');
            $stmt->execute([$id]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$company) {
                throw new Exception('Company not found');
            }

            // For testing purposes, just update the sync time
            $stmt = $this->db->prepare('
                UPDATE companies 
                SET last_synced = datetime("now")
                WHERE id = ?
            ');
            $stmt->execute([$id]);

            return ['success' => true, 'message' => 'Company synced'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}