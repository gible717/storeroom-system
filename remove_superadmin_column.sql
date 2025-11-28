-- remove_superadmin_column.sql - Remove SuperAdmin role from system
-- Execute this after updating all PHP files

-- Step 1: Check current SuperAdmin users (for reference)
SELECT ID_staf, nama, is_admin, is_superadmin
FROM staf
WHERE is_superadmin = 1;

-- Step 2: Convert SuperAdmin users to regular Admins (if any exist)
UPDATE staf
SET is_admin = 1
WHERE is_superadmin = 1 AND is_admin = 0;

-- Step 3: Remove the is_superadmin column
ALTER TABLE staf DROP COLUMN is_superadmin;

-- Step 4: Verification - Show updated table structure
DESCRIBE staf;

-- Step 5: Verify all users now only have Admin/Staff roles
SELECT ID_staf, nama, is_admin,
    CASE
        WHEN is_admin = 1 THEN 'Admin'
        ELSE 'Staf'
    END AS peranan
FROM staf
ORDER BY is_admin DESC, nama ASC;
