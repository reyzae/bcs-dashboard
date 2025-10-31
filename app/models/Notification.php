<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Bytebalok Notification Model
 * Handles system notifications
 */

class Notification extends BaseModel {
    protected $table = 'notifications';
    protected $fillable = [
        'type', 'title', 'message', 'data', 'is_read', 'user_id', 'order_id'
    ];
    
    /**
     * Create notification
     */
    public function createNotification($type, $title, $message, $userId = null, $orderId = null, $data = null) {
        $notificationData = [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'user_id' => $userId,
            'order_id' => $orderId,
            'data' => $data ? json_encode($data) : null
        ];
        
        return $this->create($notificationData);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId) {
        return $this->update($notificationId, ['is_read' => 1]);
    }
    
    /**
     * Mark all user notifications as read
     */
    public function markAllAsRead($userId) {
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE user_id = ? AND is_read = 0";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId]);
    }
    
    /**
     * Get unread notifications
     */
    public function getUnread($userId = null) {
        if ($userId) {
            return $this->findAll(['user_id' => $userId, 'is_read' => 0], 'created_at DESC');
        }
        return $this->findAll(['is_read' => 0], 'created_at DESC');
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 20) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? OR user_id IS NULL 
                ORDER BY created_at DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get order notifications
     */
    public function getOrderNotifications($orderId) {
        return $this->findAll(['order_id' => $orderId], 'created_at DESC');
    }
    
    /**
     * Count unread notifications
     */
    public function countUnread($userId = null) {
        if ($userId) {
            return $this->count(['user_id' => $userId, 'is_read' => 0]);
        }
        return $this->count(['is_read' => 0]);
    }
    
    /**
     * Delete old notifications
     */
    public function deleteOldNotifications($days = 30) {
        $sql = "DELETE FROM {$this->table} 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY) 
                AND is_read = 1";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$days]);
    }
    
    /**
     * Notify new order
     */
    public function notifyNewOrder($orderId, $orderNumber, $amount) {
        return $this->createNotification(
            'order',
            'New Order Received',
            "Order #{$orderNumber} - Total: Rp " . number_format($amount, 0, ',', '.'),
            null, // Send to all staff
            $orderId,
            ['order_number' => $orderNumber, 'amount' => $amount]
        );
    }
    
    /**
     * Notify payment received
     */
    public function notifyPaymentReceived($orderId, $orderNumber, $amount) {
        return $this->createNotification(
            'payment',
            'Payment Received',
            "Payment confirmed for Order #{$orderNumber} - Rp " . number_format($amount, 0, ',', '.'),
            null,
            $orderId,
            ['order_number' => $orderNumber, 'amount' => $amount]
        );
    }
    
    /**
     * Notify order status change
     */
    public function notifyOrderStatusChange($orderId, $orderNumber, $status) {
        $statusMessages = [
            'processing' => 'Your order is being processed',
            'ready' => 'Your order is ready for pickup/delivery',
            'completed' => 'Your order has been completed',
            'cancelled' => 'Your order has been cancelled'
        ];
        
        $message = $statusMessages[$status] ?? "Order status updated to: {$status}";
        
        return $this->createNotification(
            'order',
            'Order Status Update',
            "Order #{$orderNumber}: {$message}",
            null,
            $orderId,
            ['order_number' => $orderNumber, 'status' => $status]
        );
    }
}

