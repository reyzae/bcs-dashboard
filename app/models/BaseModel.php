<?php
/**
 * Bytebalok Base Model
 * Abstract base class for all models with common functionality
 */

abstract class BaseModel {
    protected $pdo;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $timestamps = true;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Find record by ID
     */
    public function find($id) {
        try {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("❌ Query failed in find(): " . json_encode($errorInfo));
                return false;
            }
            
        return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("❌ PDO Error in find(): " . $e->getMessage());
            error_log("   Table: {$this->table}, ID: {$id}");
            return false;
        }
    }
    
    /**
     * Find all records with optional conditions
     */
    public function findAll($conditions = [], $orderBy = null, $limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $whereClause);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
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
     * Create new record
     */
    public function create($data) {
        try {
        // Debug: Log data before filterFillable
        error_log('🗄️ BaseModel->create() - Table: ' . $this->table);
        error_log('📥 Data BEFORE filterFillable: ' . json_encode($data));
        error_log('🔑 Fillable fields: ' . json_encode($this->fillable));
        
        $data = $this->filterFillable($data);
        
        // Debug: Log data after filterFillable
        error_log('📤 Data AFTER filterFillable: ' . json_encode($data));
        
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        // Debug: Log SQL
        error_log('🔧 SQL Query: ' . $sql);
        error_log('💾 Values: ' . json_encode(array_values($data)));
        
        $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute(array_values($data));
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log('❌ Execute failed: ' . json_encode($errorInfo));
                throw new Exception("Failed to insert into {$this->table}: " . $errorInfo[2]);
            }
        
        $insertedId = $this->pdo->lastInsertId();
        error_log('✅ Inserted with ID: ' . $insertedId);
        
        return $insertedId;
        } catch (PDOException $e) {
            error_log('❌ PDO Error in BaseModel->create(): ' . $e->getMessage());
            error_log('❌ SQL State: ' . $e->getCode());
            throw new Exception("Database error creating record in {$this->table}: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Update record by ID
     */
    public function update($id, $data) {
        // Debug: Log data before filterFillable
        error_log('🗄️ BaseModel->update() - Table: ' . $this->table . ' | ID: ' . $id);
        error_log('📥 Data BEFORE filterFillable: ' . json_encode($data));
        
        $data = $this->filterFillable($data);
        
        // Debug: Log data after filterFillable
        error_log('📤 Data AFTER filterFillable: ' . json_encode($data));
        
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $fields = array_keys($data);
        $setClause = array_map(function($field) { return "{$field} = ?"; }, $fields);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE {$this->primaryKey} = ?";
        $params = array_merge(array_values($data), [$id]);
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete record by ID
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Count records with optional conditions
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $whereClause);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Execute raw SQL query
     */
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Filter data to only include fillable fields
     */
    protected function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Hide sensitive fields from output
     */
    public function hideFields($data) {
        if (empty($this->hidden)) {
            return $data;
        }
        
        return array_diff_key($data, array_flip($this->hidden));
    }
}
