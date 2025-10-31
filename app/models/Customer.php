<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Bytebalok Customer Model
 * Handles customer management
 */

class Customer extends BaseModel {
    protected $table = 'customers';
    protected $fillable = [
        'customer_code', 'name', 'email', 'phone', 'address', 
        'city', 'postal_code', 'is_active'
    ];
    
    /**
     * Get customer with transaction statistics
     */
    public function findWithStats($id) {
        $customer = $this->find($id);
        if (!$customer) return null;
        
        // Get transaction stats
        $sql = "SELECT 
                    COUNT(*) as total_transactions,
                    SUM(total_amount) as total_spent,
                    AVG(total_amount) as average_transaction,
                    MAX(created_at) as last_purchase
                FROM transactions 
                WHERE customer_id = ? AND status = 'completed'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $stats = $stmt->fetch();
        
        $customer['stats'] = $stats;
        return $customer;
    }
    
    /**
     * Get all customers with transaction statistics
     */
    public function findAllWithStats($conditions = [], $orderBy = 'name ASC', $limit = null, $offset = null) {
        $sql = "SELECT c.*, 
                    COUNT(t.id) as total_transactions,
                    COALESCE(SUM(t.total_amount), 0) as total_spent,
                    MAX(t.created_at) as last_purchase
                FROM {$this->table} c 
                LEFT JOIN transactions t ON c.id = t.customer_id AND t.status = 'completed'
                WHERE c.is_active = 1";
        $params = [];
        
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                $sql .= " AND c.{$field} = ?";
                $params[] = $value;
            }
        }
        
        $sql .= " GROUP BY c.id";
        
        if ($orderBy) {
            $sql .= " ORDER BY c.{$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Search customers by name, email, or phone
     */
    public function search($query, $limit = 20) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR customer_code LIKE ?)
                ORDER BY name 
                LIMIT {$limit}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["%{$query}%", "%{$query}%", "%{$query}%", "%{$query}%"]);
        return $stmt->fetchAll();
    }
    
    /**
     * Generate unique customer code
     */
    public function generateCustomerCode() {
        $prefix = 'CUST';
        $date = date('Ymd');
        
        // Get last customer code for today
        $sql = "SELECT customer_code FROM {$this->table} 
                WHERE customer_code LIKE ? 
                ORDER BY customer_code DESC 
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$prefix . $date . '%']);
        $lastCustomer = $stmt->fetch();
        
        if ($lastCustomer) {
            $lastNumber = intval(substr($lastCustomer['customer_code'], -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Check if customer code exists
     */
    public function codeExists($code, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE customer_code = ?";
        $params = [$code];
        
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
        if (empty($email)) return false;
        
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
     * Get customer statistics
     */
    public function getStats() {
        $stats = [];
        
        // Total customers
        $stats['total'] = $this->count();
        
        // Active customers
        $stats['active'] = $this->count(['is_active' => 1]);
        
        // This month's new customers
        $thisMonth = date('Y-m-01');
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE is_active = 1 AND created_at >= ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$thisMonth]);
        $result = $stmt->fetch();
        $stats['this_month'] = $result['count'];
        
        // Customers with transactions
        $sql = "SELECT COUNT(DISTINCT c.id) as count 
                FROM {$this->table} c 
                INNER JOIN transactions t ON c.id = t.customer_id 
                WHERE c.is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['with_transactions'] = $result['count'];
        
        // Top customers by spending
        $sql = "SELECT c.name, c.customer_code, SUM(t.total_amount) as total_spent
                FROM {$this->table} c 
                INNER JOIN transactions t ON c.id = t.customer_id 
                WHERE c.is_active = 1 AND t.status = 'completed'
                GROUP BY c.id 
                ORDER BY total_spent DESC 
                LIMIT 5";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stats['top_customers'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    /**
     * Get top customers by total spent
     */
    public function getTopCustomers($limit = 10) {
        $sql = "SELECT 
                    c.id,
                    c.name,
                    c.customer_code,
                    c.phone,
                    c.email,
                    COUNT(t.id) as total_orders,
                    COALESCE(SUM(t.total_amount), 0) as total_spent
                FROM {$this->table} c 
                LEFT JOIN transactions t ON c.id = t.customer_id AND t.status = 'completed'
                WHERE c.is_active = 1
                GROUP BY c.id 
                HAVING total_orders > 0
                ORDER BY total_spent DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
