<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Bytebalok Stock Movement Model
 * Tracks all inventory movements for products
 */

class StockMovement extends BaseModel {
    protected $table = 'stock_movements';
    protected $fillable = [
        'product_id', 'movement_type', 'quantity', 
        'reference_type', 'reference_id', 'user_id', 'notes'
    ];
    protected $timestamps = false; // Only created_at
    
    /**
     * Record a stock movement
     */
    public function recordMovement($productId, $quantity, $movementType, $referenceType = null, $referenceId = null, $userId = null, $notes = null) {
        $data = [
            'product_id' => $productId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'user_id' => $userId,
            'notes' => $notes
        ];
        
        try {
            $sql = "INSERT INTO {$this->table} 
                    (product_id, movement_type, quantity, reference_type, reference_id, user_id, notes, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['product_id'],
                $data['movement_type'],
                $data['quantity'],
                $data['reference_type'],
                $data['reference_id'],
                $data['user_id'],
                $data['notes']
            ]);
        } catch (Exception $e) {
            error_log("Failed to record stock movement: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get movements by product
     */
    public function getByProduct($productId, $limit = 50) {
        $sql = "SELECT sm.*, p.name as product_name, p.sku, u.full_name as user_name 
                FROM {$this->table} sm 
                LEFT JOIN products p ON sm.product_id = p.id 
                LEFT JOIN users u ON sm.user_id = u.id 
                WHERE sm.product_id = ? 
                ORDER BY sm.created_at DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get movements by type
     */
    public function getByType($movementType, $limit = 50) {
        $sql = "SELECT sm.*, p.name as product_name, p.sku, u.full_name as user_name 
                FROM {$this->table} sm 
                LEFT JOIN products p ON sm.product_id = p.id 
                LEFT JOIN users u ON sm.user_id = u.id 
                WHERE sm.movement_type = ? 
                ORDER BY sm.created_at DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$movementType, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get movements by reference
     */
    public function getByReference($referenceType, $referenceId) {
        $sql = "SELECT sm.*, p.name as product_name, p.sku, u.full_name as user_name 
                FROM {$this->table} sm 
                LEFT JOIN products p ON sm.product_id = p.id 
                LEFT JOIN users u ON sm.user_id = u.id 
                WHERE sm.reference_type = ? AND sm.reference_id = ? 
                ORDER BY sm.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$referenceType, $referenceId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get recent movements
     */
    public function getRecent($limit = 50) {
        $sql = "SELECT sm.*, p.name as product_name, p.sku, u.full_name as user_name 
                FROM {$this->table} sm 
                LEFT JOIN products p ON sm.product_id = p.id 
                LEFT JOIN users u ON sm.user_id = u.id 
                ORDER BY sm.created_at DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get movements with filters
     */
    public function getFiltered($filters = [], $limit = 50, $offset = 0) {
        $sql = "SELECT sm.*, p.name as product_name, p.sku, u.full_name as user_name 
                FROM {$this->table} sm 
                LEFT JOIN products p ON sm.product_id = p.id 
                LEFT JOIN users u ON sm.user_id = u.id 
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['product_id'])) {
            $sql .= " AND sm.product_id = ?";
            $params[] = $filters['product_id'];
        }
        
        if (isset($filters['movement_type'])) {
            $sql .= " AND sm.movement_type = ?";
            $params[] = $filters['movement_type'];
        }
        
        if (isset($filters['user_id'])) {
            $sql .= " AND sm.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (isset($filters['reference_type'])) {
            $sql .= " AND sm.reference_type = ?";
            $params[] = $filters['reference_type'];
        }
        
        if (isset($filters['start_date'])) {
            $sql .= " AND DATE(sm.created_at) >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (isset($filters['end_date'])) {
            $sql .= " AND DATE(sm.created_at) <= ?";
            $params[] = $filters['end_date'];
        }
        
        $sql .= " ORDER BY sm.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get stock movement summary by product
     */
    public function getSummaryByProduct($productId, $startDate = null, $endDate = null) {
        $sql = "SELECT 
                    movement_type,
                    COUNT(*) as total_movements,
                    SUM(quantity) as total_quantity
                FROM {$this->table} 
                WHERE product_id = ?";
        $params = [$productId];
        
        if ($startDate) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " GROUP BY movement_type";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get daily stock movement report
     */
    public function getDailyReport($startDate, $endDate) {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    movement_type,
                    COUNT(*) as movements_count,
                    SUM(quantity) as total_quantity
                FROM {$this->table} 
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at), movement_type
                ORDER BY date DESC, movement_type";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
}

