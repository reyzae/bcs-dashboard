<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Bytebalok Payment Model
 * Handles payment transactions and QR code generation
 */

class Payment extends BaseModel {
    protected $table = 'payments';
    protected $fillable = [
        'order_id', 'payment_method', 'amount', 'status', 'transaction_id',
        'qr_code', 'qr_string', 'payment_url', 'expired_at', 'paid_at', 'callback_data'
    ];
    
    /**
     * Create payment record
     */
    public function createPayment($orderData) {
        $paymentData = [
            'order_id' => $orderData['order_id'],
            'payment_method' => $orderData['payment_method'],
            'amount' => $orderData['amount'],
            'status' => 'pending',
            'expired_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ];
        
        return $this->create($paymentData);
    }
    
    /**
     * Generate QRIS code using Payment Gateway Service
     */
    public function generateQRIS($orderId, $amount, $customerData = []) {
        require_once __DIR__ . '/../services/PaymentGatewayService.php';
        
        $gateway = $_ENV['PAYMENT_GATEWAY'] ?? 'midtrans';
        $gatewayService = new PaymentGatewayService($gateway);
        
        $result = $gatewayService->createQRIS($orderId, $amount, $customerData);
        
        return [
            'qr_string' => $result['qr_string'] ?? null,
            'qr_code_url' => $result['qr_code_url'] ?? null,
            'payment_url' => $result['payment_url'] ?? null,
            'transaction_id' => $result['transaction_id'] ?? null,
            'expired_at' => $result['expired_at'] ?? date('Y-m-d H:i:s', strtotime('+24 hours')),
            'gateway_response' => $result['gateway_response'] ?? null
        ];
    }
    
    /**
     * Generate Bank Transfer payment info
     */
    public function generateBankTransfer($orderId, $amount, $bank = 'bca') {
        require_once __DIR__ . '/../services/PaymentGatewayService.php';
        
        $gateway = $_ENV['PAYMENT_GATEWAY'] ?? 'midtrans';
        $gatewayService = new PaymentGatewayService($gateway);
        
        $result = $gatewayService->createBankTransfer($orderId, $amount, $bank);
        
        return $result;
    }
    
    /**
     * Generate Card Payment info
     */
    public function generateCardPayment($orderId, $amount, $customerData = []) {
        require_once __DIR__ . '/../services/PaymentGatewayService.php';
        
        $gateway = $_ENV['PAYMENT_GATEWAY'] ?? 'midtrans';
        $gatewayService = new PaymentGatewayService($gateway);
        
        $result = $gatewayService->createCardPayment($orderId, $amount, $customerData);
        
        return $result;
    }
    
    /**
     * Update payment status
     */
    public function updateStatus($paymentId, $status, $transactionId = null, $callbackData = null) {
        $updateData = ['status' => $status];
        
        if ($transactionId) {
            $updateData['transaction_id'] = $transactionId;
        }
        
        if ($callbackData) {
            $updateData['callback_data'] = json_encode($callbackData);
        }
        
        if ($status === 'success') {
            $updateData['paid_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($paymentId, $updateData);
    }
    
    /**
     * Get payment by order ID
     */
    public function findByOrderId($orderId) {
        $sql = "SELECT * FROM {$this->table} WHERE order_id = ? ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }
    
    /**
     * Get payment by transaction ID
     */
    public function findByTransactionId($transactionId) {
        $sql = "SELECT * FROM {$this->table} WHERE transaction_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$transactionId]);
        return $stmt->fetch();
    }
    
    /**
     * Check expired payments and update status
     */
    public function checkExpiredPayments() {
        $sql = "UPDATE {$this->table} 
                SET status = 'expired' 
                WHERE status = 'pending' 
                AND expired_at < NOW()";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute();
    }
    
    /**
     * Get pending payments
     */
    public function getPendingPayments() {
        return $this->findAll(['status' => 'pending'], 'created_at DESC');
    }
    
    /**
     * Get payment statistics
     */
    public function getStats($startDate = null, $endDate = null) {
        $sql = "SELECT 
                    COUNT(*) as total_payments,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_payments,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_payments,
                    SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as total_amount,
                    AVG(CASE WHEN status = 'success' THEN amount ELSE NULL END) as average_amount
                FROM {$this->table}";
        $params = [];
        
        if ($startDate) {
            $sql .= " WHERE DATE(created_at) >= ?";
            $params[] = $startDate;
            if ($endDate) {
                $sql .= " AND DATE(created_at) <= ?";
                $params[] = $endDate;
            }
        } elseif ($endDate) {
            $sql .= " WHERE DATE(created_at) <= ?";
            $params[] = $endDate;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}

