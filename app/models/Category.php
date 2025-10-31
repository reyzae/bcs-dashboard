<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Bytebalok Category Model
 * Handles product category management
 */

class Category extends BaseModel {
    protected $table = 'categories';
    protected $fillable = [
        'name', 'description', 'color', 'icon', 'is_active'
    ];
    
    /**
     * Get categories with product count
     */
    public function findAllWithProductCount() {
        $sql = "SELECT c.*, COUNT(p.id) as product_count 
                FROM {$this->table} c 
                LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1 
                WHERE c.is_active = 1 
                GROUP BY c.id 
                ORDER BY c.name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get active categories only
     */
    public function getActive() {
        return $this->findAll(['is_active' => 1], 'name ASC');
    }
    
    /**
     * Check if category name exists
     */
    public function nameExists($name, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = ?";
        $params = [$name];
        
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
     * Get category statistics
     */
    public function getStats() {
        $stats = [];
        
        // Total categories
        $stats['total'] = $this->count();
        
        // Active categories
        $stats['active'] = $this->count(['is_active' => 1]);
        
        // Categories with products
        $sql = "SELECT COUNT(DISTINCT c.id) as count 
                FROM {$this->table} c 
                INNER JOIN products p ON c.id = p.category_id 
                WHERE c.is_active = 1 AND p.is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['with_products'] = $result['count'];
        
        return $stats;
    }
}
