-- Verification Script for POS Setup
-- Run this after importing database_migration_pos_enhancements.sql

-- Make sure we're using the correct database
USE bytebalok_dashboard;

-- ========================================
-- PART 1: Check Table & Column Existence
-- ========================================

-- 1. Check if hold_transactions table exists
SELECT 'hold_transactions table' as Check_Item, 
       IF(COUNT(*) > 0, 'EXISTS ✓', 'MISSING ✗') as Status
FROM information_schema.tables 
WHERE table_schema = 'bytebalok_dashboard' 
AND table_name = 'hold_transactions';

-- 2. Check if products.barcode column exists
SELECT 'products.barcode column' as Check_Item,
       IF(COUNT(*) > 0, 'EXISTS ✓', 'MISSING ✗') as Status
FROM information_schema.columns 
WHERE table_schema = 'bytebalok_dashboard' 
AND table_name = 'products' 
AND column_name = 'barcode';

-- 3. Check if cashier role exists
SELECT 'cashier role in users' as Check_Item,
       IF(COLUMN_TYPE LIKE '%cashier%', 'EXISTS ✓', 'MISSING ✗') as Status
FROM information_schema.columns 
WHERE table_schema = 'bytebalok_dashboard' 
AND table_name = 'users' 
AND column_name = 'role';

-- ========================================
-- PART 2: Check Data Existence
-- ========================================

-- 4. Count active products (should have some for POS to work)
SELECT 'Active products' as Check_Item,
       CONCAT(COUNT(*), ' products found') as Status
FROM bytebalok_dashboard.products 
WHERE is_active = 1;

-- 5. Count categories (should have some for filtering)
SELECT 'Active categories' as Check_Item,
       CONCAT(COUNT(*), ' categories found') as Status
FROM bytebalok_dashboard.categories 
WHERE is_active = 1;

-- 6. Check transactions table
SELECT 'transactions table' as Check_Item,
       CONCAT('Ready (', COUNT(*), ' transactions)') as Status
FROM bytebalok_dashboard.transactions;

-- 7. Check transaction_items table
SELECT 'transaction_items table' as Check_Item,
       CONCAT('Ready (', COUNT(*), ' items)') as Status
FROM bytebalok_dashboard.transaction_items;

-- 8. Check stock_movements table
SELECT 'stock_movements table' as Check_Item,
       CONCAT('Ready (', COUNT(*), ' movements)') as Status
FROM bytebalok_dashboard.stock_movements;

-- 9. Check if settings table exists (from new enhancements)
SELECT 'settings table' as Check_Item,
       IF(COUNT(*) > 0, 'EXISTS ✓', 'MISSING ✗') as Status
FROM information_schema.tables 
WHERE table_schema = 'bytebalok_dashboard' 
AND table_name = 'settings';

-- 10. Check if notifications table exists (from new enhancements)
SELECT 'notifications table' as Check_Item,
       IF(COUNT(*) > 0, 'EXISTS ✓', 'MISSING ✗') as Status
FROM information_schema.tables 
WHERE table_schema = 'bytebalok_dashboard' 
AND table_name = 'notifications';

-- ========================================
-- PART 3: Summary
-- ========================================

SELECT '====== VERIFICATION COMPLETE ======' as Summary;
SELECT 'If all checks show EXISTS ✓, your system is ready!' as Note;
SELECT 'Next Steps:' as Action,
       '1. Clear browser cache (Ctrl+F5)' as Step1,
       '2. Test dashboards at /dashboard/' as Step2,
       '3. Test POS at /dashboard/pos.php' as Step3;

