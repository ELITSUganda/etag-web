-- =====================================================
-- BUTCHERY PACKAGING MENU SETUP
-- Run this SQL to add Packaging menu with 3 sub-menus
-- =====================================================

-- Step 1: Add "Packaging" parent menu item
-- Adjust the parent_id and order values based on your menu structure
-- If you want it under "Butchery", set parent_id to Butchery's menu ID
-- Otherwise, set parent_id to 0 for root level

INSERT INTO admin_menu (parent_id, `order`, title, icon, uri, created_at, updated_at)
VALUES 
(0, 100, 'Packaging', 'fa-archive', NULL, NOW(), NOW());

-- Step 2: Get the ID of the Packaging menu just created
SET @packaging_id = LAST_INSERT_ID();

-- Step 3: Add three sub-menus under Packaging
INSERT INTO admin_menu (parent_id, `order`, title, icon, uri, created_at, updated_at)
VALUES 
(@packaging_id, 1, 'Primal Cuts Fore Quarters', 'fa-cut', 'packaging-fore-quarters', NOW(), NOW()),
(@packaging_id, 2, 'Primal Cuts Hind Quarters', 'fa-cut', 'packaging-hind-quarters', NOW(), NOW()),
(@packaging_id, 3, 'Offals', 'fa-cubes', 'packaging-offals', NOW(), NOW());

-- Step 4: Verify the menu items were created
SELECT id, parent_id, title, icon, uri, `order` 
FROM admin_menu 
WHERE title IN ('Packaging', 'Primal Cuts Fore Quarters', 'Primal Cuts Hind Quarters', 'Offals')
ORDER BY parent_id, `order`;

-- =====================================================
-- ALTERNATIVE: If you want Packaging under existing Butchery menu
-- =====================================================

-- First, find your Butchery menu ID:
-- SELECT id FROM admin_menu WHERE title LIKE '%Butchery%';

-- Then run these commands, replacing XX with the actual Butchery menu ID:

-- INSERT INTO admin_menu (parent_id, `order`, title, icon, uri, created_at, updated_at)
-- VALUES 
-- (XX, 100, 'Packaging', 'fa-archive', NULL, NOW(), NOW());
-- 
-- SET @packaging_id = LAST_INSERT_ID();
-- 
-- INSERT INTO admin_menu (parent_id, `order`, title, icon, uri, created_at, updated_at)
-- VALUES 
-- (@packaging_id, 1, 'Primal Cuts Fore Quarters', 'fa-cut', 'packaging-fore-quarters', NOW(), NOW()),
-- (@packaging_id, 2, 'Primal Cuts Hind Quarters', 'fa-cut', 'packaging-hind-quarters', NOW(), NOW()),
-- (@packaging_id, 3, 'Offals', 'fa-cubes', 'packaging-offals', NOW(), NOW());

-- =====================================================
-- CLEANUP (if needed to remove and start over)
-- =====================================================

-- DELETE FROM admin_menu 
-- WHERE title IN ('Packaging', 'Primal Cuts Fore Quarters', 'Primal Cuts Hind Quarters', 'Offals');
