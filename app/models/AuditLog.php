<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Bytebalok Audit Log Model
 * Handles logging of all user actions for security and tracking
 */

class AuditLog extends BaseModel {
    protected $table = 'audit_logs';
    protected $fillable = [
        'user_id', 'action', 'table_name', 'record_id', 
        'old_values', 'new_values', 'ip_address', 'user_agent'
    ];
    protected $timestamps = false; // Only created_at
    
    /**
     * Log an action
     */
    public function logAction($userId, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        try {
            $sql = "INSERT INTO {$this->table} 
                    (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['user_id'],
                $data['action'],
                $data['table_name'],
                $data['record_id'],
                $data['old_values'],
                $data['new_values'],
                $data['ip_address'],
                $data['user_agent']
            ]);
        } catch (Exception $e) {
            error_log("Failed to log action: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get logs by user
     */
    public function getByUser($userId, $limit = 50) {
        $sql = "SELECT al.*, u.full_name as user_name 
                FROM {$this->table} al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE al.user_id = ? 
                ORDER BY al.created_at DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get logs by table
     */
    public function getByTable($tableName, $recordId = null, $limit = 50) {
        $sql = "SELECT al.*, u.full_name as user_name 
                FROM {$this->table} al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE al.table_name = ?";
        $params = [$tableName];
        
        if ($recordId) {
            $sql .= " AND al.record_id = ?";
            $params[] = $recordId;
        }
        
        $sql .= " ORDER BY al.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get recent logs
     */
    public function getRecent($limit = 50) {
        $sql = "SELECT al.*, u.full_name as user_name 
                FROM {$this->table} al 
                LEFT JOIN users u ON al.user_id = u.id 
                ORDER BY al.created_at DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get logs by action
     */
    public function getByAction($action, $limit = 50) {
        $sql = "SELECT al.*, u.full_name as user_name 
                FROM {$this->table} al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE al.action = ? 
                ORDER BY al.created_at DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$action, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get logs with filters
     */
    public function getFiltered($filters = [], $limit = 50, $offset = 0) {
        $sql = "SELECT al.*, u.full_name as user_name 
                FROM {$this->table} al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (isset($filters['action'])) {
            $sql .= " AND al.action = ?";
            $params[] = $filters['action'];
        }
        
        if (isset($filters['table_name'])) {
            $sql .= " AND al.table_name = ?";
            $params[] = $filters['table_name'];
        }
        
        if (isset($filters['start_date'])) {
            $sql .= " AND DATE(al.created_at) >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (isset($filters['end_date'])) {
            $sql .= " AND DATE(al.created_at) <= ?";
            $params[] = $filters['end_date'];
        }
        
        $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Count logs with filters
     */
    public function countFiltered($filters = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (isset($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (isset($filters['action'])) {
            $sql .= " AND action = ?";
            $params[] = $filters['action'];
        }
        
        if (isset($filters['table_name'])) {
            $sql .= " AND table_name = ?";
            $params[] = $filters['table_name'];
        }
        
        if (isset($filters['start_date'])) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (isset($filters['end_date'])) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $filters['end_date'];
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }
}

