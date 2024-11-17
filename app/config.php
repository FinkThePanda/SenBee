<?php
// app/config.php

// Development mode
define('DEVELOPMENT', true);

// Database configuration
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            // Use ROOT_PATH constant to ensure correct path
            $dbPath = dirname(__DIR__) . '/data/companies.db';
            
            // Check if database file exists
            if (!file_exists($dbPath)) {
                throw new Exception("Database file not found: $dbPath");
            }

            $this->conn = new PDO('sqlite:' . $dbPath);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}

// API Configuration
define('CVR_API_URL', 'https://cvrapi.dk/api');
define('CVR_COUNTRY', 'dk');