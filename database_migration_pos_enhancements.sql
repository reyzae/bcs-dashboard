-- POS System Enhancements Migration
-- Adds hold transactions table for POS

-- Table for holding transactions (save for later)
CREATE TABLE IF NOT EXISTS `hold_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `cart_data` TEXT NOT NULL COMMENT 'JSON encoded cart data',
  `notes` TEXT DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_hold_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hold_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add barcode column to products table if not exists
ALTER TABLE `products` 
ADD COLUMN IF NOT EXISTS `barcode` varchar(100) DEFAULT NULL AFTER `sku`,
ADD KEY IF NOT EXISTS `idx_barcode` (`barcode`);

-- Add cashier role to users if not exists
ALTER TABLE `users` 
MODIFY COLUMN `role` enum('admin','manager','staff','cashier') NOT NULL DEFAULT 'staff';

COMMIT;

