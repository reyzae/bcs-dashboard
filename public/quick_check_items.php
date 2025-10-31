<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../app/config/database.php';

header('Content-Type: application/json');

try {
    // Check last 2 transactions
    $sql = "SELECT 
        t.id,
        t.transaction_number,
        t.total_amount,
        COUNT(ti.id) as item_rows,
        COALESCE(SUM(ti.quantity), 0) as total_quantity
    FROM transactions t
    LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
    WHERE t.status = 'completed'
    GROUP BY t.id
    ORDER BY t.created_at DESC
    LIMIT 2";
    
    $stmt = $pdo->query($sql);
    $result = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $result,
        'message' => count($result) . ' transactions checked'
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

