<?php
/**
 * Bytebalok Database Configuration
 * Modern database connection with error handling and security
 */

class Database {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    private $pdo;
    
    public function __construct() {
        $hostConfig = $_ENV['DB_HOST'] ?? 'localhost';
        
        // Handle host with port (e.g., localhost:33066)
        if (strpos($hostConfig, ':') !== false) {
            list($this->host, $this->port) = explode(':', $hostConfig);
        } else {
            $this->host = $hostConfig;
            $this->port = '3306';
        }
        
        $this->db_name = $_ENV['DB_NAME'] ?? 'bytebalok_dashboard';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
        $this->charset = 'utf8mb4';
    }
    
    public function getConnection() {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset={$this->charset}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
                ];
                
                $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new Exception("Database connection failed");
            }
        }
        
        return $this->pdo;
    }
    
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }
    
    public function commit() {
        return $this->getConnection()->commit();
    }
    
    public function rollback() {
        return $this->getConnection()->rollback();
    }
}

// Global database instance
$database = new Database();
$pdo = $database->getConnection();