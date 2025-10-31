-- Bytebalok Database Fixes & Enhancements
-- Adding missing tables and features from workflow documentation
-- Date: 2025-01-26

-- ========================================
-- 1. ADD MISSING TABLE: hold_transactions
-- ========================================

CREATE TABLE IF NOT EXISTS `hold_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'Kasir yang hold transaction',
  `customer_id` int(11) DEFAULT NULL,
  `cart_data` json NOT NULL COMMENT 'Cart items dalam JSON format',
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `hold_transactions_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hold_transactions_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 2. ADD MISSING TABLE: settings (if not exists)
-- ========================================

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') NOT NULL DEFAULT 'string',
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 3. ADD MISSING TABLE: notifications
-- ========================================

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL = for all users',
  `type` enum('info','success','warning','error') NOT NULL DEFAULT 'info',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_read` (`is_read`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 4. ADD MISSING TABLE: user_sessions
-- ========================================

CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL UNIQUE,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_token` (`session_token`),
  KEY `idx_activity` (`last_activity`),
  CONSTRAINT `user_sessions_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 5. ENHANCE transactions table
-- ========================================

-- Add column for cashier performance tracking
ALTER TABLE `transactions` 
ADD COLUMN IF NOT EXISTS `served_by` varchar(100) DEFAULT NULL COMMENT 'Cashier full name' AFTER `user_id`,
ADD COLUMN IF NOT EXISTS `cash_received` decimal(12,2) DEFAULT NULL COMMENT 'Cash received for change calculation' AFTER `payment_method`,
ADD COLUMN IF NOT EXISTS `cash_change` decimal(12,2) DEFAULT NULL COMMENT 'Change given' AFTER `cash_received`;

-- ========================================
-- 6. ENHANCE products table
-- ========================================

-- Add columns for better inventory management
ALTER TABLE `products`
ADD COLUMN IF NOT EXISTS `reorder_point` int(11) DEFAULT NULL COMMENT 'Auto reorder when stock below this' AFTER `min_stock_level`,
ADD COLUMN IF NOT EXISTS `supplier_info` varchar(255) DEFAULT NULL COMMENT 'Supplier name/contact' AFTER `unit`,
ADD COLUMN IF NOT EXISTS `last_restock_date` date DEFAULT NULL COMMENT 'Last time restocked' AFTER `barcode`,
ADD COLUMN IF NOT EXISTS `expiry_date` date DEFAULT NULL COMMENT 'For perishable items' AFTER `last_restock_date`;

-- ========================================
-- 7. ENHANCE customers table
-- ========================================

-- Add columns for customer loyalty
ALTER TABLE `customers`
ADD COLUMN IF NOT EXISTS `total_purchases` int(11) NOT NULL DEFAULT 0 COMMENT 'Total number of purchases' AFTER `postal_code`,
ADD COLUMN IF NOT EXISTS `total_spent` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Lifetime value' AFTER `total_purchases`,
ADD COLUMN IF NOT EXISTS `last_purchase_date` timestamp NULL DEFAULT NULL COMMENT 'Last transaction date' AFTER `total_spent`,
ADD COLUMN IF NOT EXISTS `customer_type` enum('walk-in','regular','vip') NOT NULL DEFAULT 'walk-in' AFTER `last_purchase_date`,
ADD COLUMN IF NOT EXISTS `notes` text DEFAULT NULL COMMENT 'Admin notes about customer' AFTER `customer_type`;

-- ========================================
-- 8. INSERT DEFAULT SETTINGS
-- ========================================

INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('company_name', 'Bytebalok', 'string', 'Company name'),
('company_address', 'Jl. Example No. 123, Jakarta', 'string', 'Company address'),
('company_phone', '+62 21 1234 5678', 'string', 'Company phone number'),
('company_email', 'info@bytebalok.com', 'string', 'Company email'),
('tax_rate', '10', 'number', 'Default tax rate percentage'),
('currency', 'IDR', 'string', 'Default currency'),
('currency_symbol', 'Rp', 'string', 'Currency symbol'),
('timezone', 'Asia/Jakarta', 'string', 'System timezone'),
('date_format', 'Y-m-d', 'string', 'Date format'),
('time_format', 'H:i:s', 'string', 'Time format'),
('low_stock_threshold', '5', 'number', 'Default low stock threshold'),
('enable_barcode_scanner', 'true', 'boolean', 'Enable barcode scanner in POS'),
('auto_print_receipt', 'false', 'boolean', 'Auto print receipt after transaction'),
('cart_autosave_interval', '30', 'number', 'Auto save cart interval in seconds'),
('session_timeout', '3600', 'number', 'Session timeout in seconds (1 hour)'),
('allow_negative_stock', 'false', 'boolean', 'Allow selling when stock is negative'),
('receipt_footer_text', 'Terima kasih atas kunjungan Anda!', 'string', 'Receipt footer message')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- ========================================
-- 9. CREATE INDEXES FOR BETTER PERFORMANCE
-- ========================================

-- Transactions performance indexes
CREATE INDEX IF NOT EXISTS `idx_txn_created_status` ON `transactions` (`created_at`, `status`);
CREATE INDEX IF NOT EXISTS `idx_txn_user_date` ON `transactions` (`user_id`, `created_at`);
CREATE INDEX IF NOT EXISTS `idx_txn_customer_date` ON `transactions` (`customer_id`, `created_at`);

-- Products performance indexes
CREATE INDEX IF NOT EXISTS `idx_product_stock` ON `products` (`stock_quantity`, `min_stock_level`);
CREATE INDEX IF NOT EXISTS `idx_product_active_category` ON `products` (`is_active`, `category_id`);

-- Stock movements performance indexes
CREATE INDEX IF NOT EXISTS `idx_stock_product_date` ON `stock_movements` (`product_id`, `created_at`);
CREATE INDEX IF NOT EXISTS `idx_stock_type_date` ON `stock_movements` (`movement_type`, `created_at`);

-- Audit logs performance indexes
CREATE INDEX IF NOT EXISTS `idx_audit_user_date` ON `audit_logs` (`user_id`, `created_at`);
CREATE INDEX IF NOT EXISTS `idx_audit_action_date` ON `audit_logs` (`action`, `created_at`);

-- ========================================
-- 10. CREATE VIEWS FOR REPORTING
-- ========================================

-- View: Low Stock Products
CREATE OR REPLACE VIEW `v_low_stock_products` AS
SELECT 
    p.id,
    p.sku,
    p.name,
    p.stock_quantity,
    p.min_stock_level,
    p.price,
    c.name as category_name,
    (p.min_stock_level - p.stock_quantity) as quantity_needed,
    (p.price * (p.min_stock_level - p.stock_quantity)) as estimated_cost
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.is_active = 1 
AND p.stock_quantity <= p.min_stock_level
ORDER BY p.stock_quantity ASC;

-- View: Today's Sales Summary
CREATE OR REPLACE VIEW `v_today_sales` AS
SELECT 
    COUNT(*) as total_transactions,
    SUM(t.total_amount) as total_sales,
    SUM(t.subtotal) as subtotal,
    SUM(t.discount_amount) as total_discounts,
    SUM(t.tax_amount) as total_tax,
    AVG(t.total_amount) as average_transaction,
    t.payment_method,
    u.full_name as cashier_name
FROM transactions t
LEFT JOIN users u ON t.user_id = u.id
WHERE DATE(t.created_at) = CURDATE()
AND t.status = 'completed'
GROUP BY t.payment_method, u.full_name;

-- View: Customer Statistics
CREATE OR REPLACE VIEW `v_customer_stats` AS
SELECT 
    c.id,
    c.customer_code,
    c.name,
    c.email,
    c.phone,
    c.customer_type,
    c.total_purchases,
    c.total_spent,
    c.last_purchase_date,
    COUNT(t.id) as verified_purchase_count,
    SUM(t.total_amount) as verified_total_spent,
    MAX(t.created_at) as verified_last_purchase,
    DATEDIFF(CURDATE(), MAX(t.created_at)) as days_since_last_purchase
FROM customers c
LEFT JOIN transactions t ON c.id = t.customer_id AND t.status = 'completed'
WHERE c.is_active = 1
GROUP BY c.id
ORDER BY c.total_spent DESC;

-- View: Product Sales Performance
CREATE OR REPLACE VIEW `v_product_performance` AS
SELECT 
    p.id,
    p.sku,
    p.name,
    p.category_id,
    c.name as category_name,
    p.price,
    p.cost_price,
    p.stock_quantity,
    COUNT(ti.id) as times_sold,
    SUM(ti.quantity) as total_quantity_sold,
    SUM(ti.total_price) as total_revenue,
    SUM(ti.total_price) - (p.cost_price * SUM(ti.quantity)) as estimated_profit,
    MAX(t.created_at) as last_sold_date
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN transaction_items ti ON p.id = ti.product_id
LEFT JOIN transactions t ON ti.transaction_id = t.id AND t.status = 'completed'
WHERE p.is_active = 1
GROUP BY p.id
ORDER BY total_quantity_sold DESC;

-- ========================================
-- 11. CREATE TRIGGERS FOR AUTO UPDATES
-- ========================================

DELIMITER $$

-- Trigger: Auto update customer stats after transaction
CREATE TRIGGER IF NOT EXISTS `trg_after_transaction_insert` 
AFTER INSERT ON `transactions`
FOR EACH ROW
BEGIN
    IF NEW.customer_id IS NOT NULL AND NEW.status = 'completed' THEN
        UPDATE customers 
        SET 
            total_purchases = total_purchases + 1,
            total_spent = total_spent + NEW.total_amount,
            last_purchase_date = NEW.created_at,
            customer_type = CASE 
                WHEN total_purchases + 1 >= 10 THEN 'vip'
                WHEN total_purchases + 1 >= 3 THEN 'regular'
                ELSE customer_type
            END
        WHERE id = NEW.customer_id;
    END IF;
END$$

-- Trigger: Auto add notification for low stock
CREATE TRIGGER IF NOT EXISTS `trg_low_stock_notification`
AFTER UPDATE ON `products`
FOR EACH ROW
BEGIN
    IF NEW.stock_quantity <= NEW.min_stock_level 
       AND OLD.stock_quantity > OLD.min_stock_level THEN
        INSERT INTO notifications (user_id, type, title, message, link)
        VALUES (
            NULL, 
            'warning', 
            'Low Stock Alert', 
            CONCAT('Product "', NEW.name, '" is running low. Current stock: ', NEW.stock_quantity),
            CONCAT('/dashboard/products.php?id=', NEW.id)
        );
    END IF;
END$$

DELIMITER ;

-- ========================================
-- 12. SAMPLE DATA FOR TESTING (Optional - comment out for production)
-- ========================================

-- Insert sample categories if not exists
INSERT IGNORE INTO `categories` (`id`, `name`, `description`, `color`, `icon`) VALUES
(1, 'Kue Balok Keju', 'Kue balok dengan topping keju premium', '#FFD700', 'fas fa-cheese'),
(2, 'Kue Balok Coklat', 'Kue balok dengan topping coklat lezat', '#8B4513', 'fas fa-cookie-bite'),
(3, 'Kue Balok Pandan', 'Kue balok dengan aroma pandan harum', '#90EE90', 'fas fa-leaf'),
(4, 'Kue Balok Mix', 'Kue balok dengan berbagai topping', '#FF69B4', 'fas fa-ice-cream'),
(5, 'Topping & Extra', 'Topping tambahan untuk kue balok', '#FFA500', 'fas fa-plus-circle');

-- Insert sample products if not exists  
INSERT IGNORE INTO `products` (`id`, `sku`, `name`, `description`, `category_id`, `price`, `cost_price`, `stock_quantity`, `min_stock_level`, `unit`) VALUES
(1, 'KB-KEJ-001', 'Kue Balok Keju Original', 'Kue balok dengan keju premium melimpah', 1, 25000.00, 15000.00, 50, 10, 'pcs'),
(2, 'KB-COK-001', 'Kue Balok Coklat Premium', 'Kue balok dengan coklat belgium', 2, 28000.00, 17000.00, 45, 10, 'pcs'),
(3, 'KB-PAN-001', 'Kue Balok Pandan Original', 'Kue balok pandan dengan aroma harum', 3, 23000.00, 14000.00, 40, 10, 'pcs'),
(4, 'KB-MIX-001', 'Kue Balok Mix Special', 'Kombinasi keju, coklat, dan pandan', 4, 30000.00, 18000.00, 35, 8, 'pcs');

-- ========================================
-- 13. STORED PROCEDURES FOR COMMON OPERATIONS
-- ========================================

DELIMITER $$

-- Procedure: Get Dashboard Statistics
CREATE PROCEDURE IF NOT EXISTS `sp_get_dashboard_stats`(IN period_days INT)
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM transactions WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL period_days DAY) AND status = 'completed') as total_transactions,
        (SELECT COALESCE(SUM(total_amount), 0) FROM transactions WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL period_days DAY) AND status = 'completed') as total_sales,
        (SELECT COUNT(*) FROM customers WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL period_days DAY)) as new_customers,
        (SELECT COUNT(*) FROM products WHERE stock_quantity <= min_stock_level AND is_active = 1) as low_stock_count,
        (SELECT COALESCE(AVG(total_amount), 0) FROM transactions WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL period_days DAY) AND status = 'completed') as avg_transaction_value;
END$$

-- Procedure: Get Top Selling Products
CREATE PROCEDURE IF NOT EXISTS `sp_get_top_products`(IN period_days INT, IN limit_count INT)
BEGIN
    SELECT 
        p.id,
        p.sku,
        p.name,
        c.name as category_name,
        COUNT(ti.id) as times_sold,
        SUM(ti.quantity) as total_quantity,
        SUM(ti.total_price) as total_revenue
    FROM products p
    INNER JOIN transaction_items ti ON p.id = ti.product_id
    INNER JOIN transactions t ON ti.transaction_id = t.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE t.status = 'completed'
    AND DATE(t.created_at) >= DATE_SUB(CURDATE(), INTERVAL period_days DAY)
    GROUP BY p.id
    ORDER BY total_quantity DESC
    LIMIT limit_count;
END$$

-- Procedure: Process Stock Adjustment
CREATE PROCEDURE IF NOT EXISTS `sp_adjust_stock`(
    IN p_product_id INT,
    IN p_adjustment_qty INT,
    IN p_movement_type ENUM('in','out','adjustment','return'),
    IN p_user_id INT,
    IN p_notes TEXT
)
BEGIN
    DECLARE current_stock INT;
    
    -- Get current stock
    SELECT stock_quantity INTO current_stock FROM products WHERE id = p_product_id;
    
    -- Update product stock
    UPDATE products 
    SET stock_quantity = stock_quantity + p_adjustment_qty,
        last_restock_date = IF(p_movement_type = 'in', CURDATE(), last_restock_date)
    WHERE id = p_product_id;
    
    -- Insert stock movement record
    INSERT INTO stock_movements (product_id, movement_type, quantity, notes, user_id)
    VALUES (p_product_id, p_movement_type, ABS(p_adjustment_qty), p_notes, p_user_id);
    
    SELECT 'Stock adjusted successfully' as message, 
           current_stock + p_adjustment_qty as new_stock;
END$$

DELIMITER ;

-- ========================================
-- COMMIT CHANGES
-- ========================================

COMMIT;

-- End of fixes and enhancements

