<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Bytebalok User Model
 * Handles user authentication and management
 */

class User extends BaseModel {
    protected $table = 'users';
    protected $fillable = [
        'username', 'email', 'password', 'full_name', 'role', 
        'phone', 'avatar', 'is_active'
    ];
    protected $hidden = ['password'];
    
    /**
     * Authenticate user login
     */
    public function login($username, $password) {
        $sql = "SELECT * FROM {$this->table} WHERE (username = ? OR email = ?) AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $this->updateLastLogin($user['id']);
            return $this->hideFields($user);
        }
        
        return false;
    }
    
    /**
     * Register new user
     */
    public function register($data) {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    /**
     * Update last login timestamp
     */
    public function updateLastLogin($userId) {
        $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE {$this->primaryKey} = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId]);
    }
    
    /**
     * Get users by role
     */
    public function getByRole($role) {
        return $this->findAll(['role' => $role, 'is_active' => 1]);
    }
    
    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Get user statistics
     */
    public function getStats() {
        $stats = [];
        
        // Total users
        $stats['total'] = $this->count();
        
        // Active users
        $stats['active'] = $this->count(['is_active' => 1]);
        
        // Users by role
        $sql = "SELECT role, COUNT(*) as count FROM {$this->table} WHERE is_active = 1 GROUP BY role";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stats['by_role'] = $stmt->fetchAll();
        
        return $stats;
    }
}