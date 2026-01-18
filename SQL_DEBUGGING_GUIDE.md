# SQL Debugging Commands for Asset Module

Use these commands to verify data integrity and diagnose issues in your Asset Management System.

## 1. Asset Maintenances Table Structure

```sql
-- Verify table structure
DESCRIBE asset_maintenances;

-- Expected columns (in order):
-- id (BIGINT UNSIGNED PK)
-- asset_id (BIGINT UNSIGNED FK)
-- maintenance_date (DATE)
-- estimated_completion_date (DATE, nullable)
-- completed_date (DATE, nullable) ← THIS IS KEY
-- description (TEXT, nullable)
-- status (ENUM('dalam_proses','selesai','dibatalkan'))
-- created_by (BIGINT UNSIGNED FK)
-- created_at (TIMESTAMP)
-- updated_at (TIMESTAMP)
```

## 2. Asset Status & Maintenance State

```sql
-- Find all active maintenance records (not completed)
SELECT 
    am.id,
    am.asset_id,
    a.asset_code,
    a.name,
    am.maintenance_date,
    am.estimated_completion_date,
    am.completed_date,
    am.status
FROM asset_maintenances am
JOIN assets a ON a.id = am.asset_id
WHERE am.completed_date IS NULL
ORDER BY am.maintenance_date DESC;

-- Find all completed maintenance
SELECT * FROM asset_maintenances
WHERE completed_date IS NOT NULL
ORDER BY completed_date DESC;

-- Find overdue maintenance
SELECT 
    am.id,
    a.asset_code,
    a.name,
    am.maintenance_date,
    am.estimated_completion_date,
    DATEDIFF(NOW(), am.estimated_completion_date) as days_overdue
FROM asset_maintenances am
JOIN assets a ON a.id = am.asset_id
WHERE am.completed_date IS NULL
  AND am.estimated_completion_date < NOW()
ORDER BY days_overdue DESC;
```

## 3. Asset Status Summary

```sql
-- Count assets by status
SELECT 
    status,
    COUNT(*) as count
FROM assets
GROUP BY status
ORDER BY count DESC;

-- Expected statuses:
-- - aktif (available for actions)
-- - dipinjam (currently borrowed)
-- - dipelihara (under maintenance)
-- - nonaktif (retired)

-- Detail breakdown
SELECT 
    'Total Active Assets' as metric,
    COUNT(*) as value
FROM assets
WHERE status != 'nonaktif'

UNION ALL

SELECT 
    'Available Assets' as metric,
    COUNT(*) as value
FROM assets
WHERE status = 'aktif' AND is_available = true

UNION ALL

SELECT 
    'Currently Borrowed' as metric,
    COUNT(*) as value
FROM assets
WHERE status = 'dipinjam'

UNION ALL

SELECT 
    'Under Maintenance' as metric,
    COUNT(*) as value
FROM assets
WHERE status = 'dipelihara'

UNION ALL

SELECT 
    'Retired/Inactive' as metric,
    COUNT(*) as value
FROM assets
WHERE status = 'nonaktif';
```

## 4. Assets with Inconsistent State

```sql
-- Find assets marked "dipelihara" but no active maintenance
SELECT a.id, a.asset_code, a.name, a.status
FROM assets a
WHERE a.status = 'dipelihara'
  AND NOT EXISTS (
    SELECT 1 FROM asset_maintenances am
    WHERE am.asset_id = a.id
      AND am.completed_date IS NULL
  );

-- Find assets with active maintenance but not marked "dipelihara"
SELECT DISTINCT a.id, a.asset_code, a.name, a.status
FROM assets a
JOIN asset_maintenances am ON am.asset_id = a.id
WHERE am.completed_date IS NULL
  AND a.status != 'dipelihara';

-- Find assets marked "dipinjam" but no active loan
SELECT a.id, a.asset_code, a.name, a.status
FROM assets a
WHERE a.status = 'dipinjam'
  AND NOT EXISTS (
    SELECT 1 FROM asset_loans al
    WHERE al.asset_id = a.id
      AND al.return_date IS NULL
  );
```

## 5. Borrowing Status Check

```sql
-- Active loans (not returned)
SELECT 
    al.id,
    a.asset_code,
    a.name,
    e.name as borrower_name,
    al.loan_date,
    al.estimated_return_date,
    DATEDIFF(NOW(), al.estimated_return_date) as days_overdue
FROM asset_loans al
JOIN assets a ON a.id = al.asset_id
JOIN employees e ON e.id = al.employee_id
WHERE al.return_date IS NULL
ORDER BY al.estimated_return_date;

-- Overdue loans
SELECT 
    al.id,
    a.asset_code,
    a.name,
    e.name as borrower_name,
    DATEDIFF(NOW(), al.estimated_return_date) as days_overdue
FROM asset_loans al
JOIN assets a ON a.id = al.asset_id
JOIN employees e ON e.id = al.employee_id
WHERE al.return_date IS NULL
  AND al.estimated_return_date < NOW();
```

## 6. Location Transfer History

```sql
-- Asset location history (newest first)
SELECT 
    at.id,
    a.asset_code,
    at.transaction_date,
    lf.name as from_location,
    lt.name as to_location,
    at.reason
FROM asset_transactions at
JOIN assets a ON a.id = at.asset_id
JOIN locations lf ON lf.id = at.from_location_id
JOIN locations lt ON lt.id = at.to_location_id
WHERE a.id = ?  -- Replace with asset ID
ORDER BY at.transaction_date DESC;

-- Current location of all assets
SELECT 
    a.id,
    a.asset_code,
    a.name,
    l.name as current_location,
    a.status
FROM assets a
JOIN locations l ON l.id = a.location_id
ORDER BY a.asset_code;
```

## 7. Category & Location Summary

```sql
-- Assets by category and status
SELECT 
    c.name as category,
    a.status,
    COUNT(*) as count
FROM assets a
JOIN asset_categories c ON c.id = a.category_id
GROUP BY c.name, a.status
ORDER BY c.name, a.status;

-- Assets by location and status
SELECT 
    l.name as location,
    a.status,
    COUNT(*) as count
FROM assets a
JOIN locations l ON l.id = a.location_id
GROUP BY l.name, a.status
ORDER BY l.name, a.status;
```

## 8. Data Integrity Checks

```sql
-- Orphaned asset records (invalid location_id)
SELECT a.id, a.asset_code
FROM assets a
LEFT JOIN locations l ON l.id = a.location_id
WHERE l.id IS NULL AND a.location_id IS NOT NULL;

-- Orphaned asset records (invalid category_id)
SELECT a.id, a.asset_code
FROM assets a
LEFT JOIN asset_categories c ON c.id = a.category_id
WHERE c.id IS NULL AND a.category_id IS NOT NULL;

-- Maintenance records with invalid asset_id
SELECT am.id
FROM asset_maintenances am
LEFT JOIN assets a ON a.id = am.asset_id
WHERE a.id IS NULL;

-- Loan records with invalid asset_id or employee_id
SELECT al.id
FROM asset_loans al
LEFT JOIN assets a ON a.id = al.asset_id
LEFT JOIN employees e ON e.id = al.employee_id
WHERE a.id IS NULL OR e.id IS NULL;
```

## 9. Recent Activity

```sql
-- Most recent maintenance activity
SELECT 
    am.maintenance_date,
    a.asset_code,
    a.name,
    am.description,
    am.completed_date
FROM asset_maintenances am
JOIN assets a ON a.id = am.asset_id
ORDER BY am.maintenance_date DESC
LIMIT 20;

-- Most recent loans
SELECT 
    al.loan_date,
    a.asset_code,
    a.name,
    e.name as employee,
    al.return_date
FROM asset_loans al
JOIN assets a ON a.id = al.asset_id
JOIN employees e ON e.id = al.employee_id
ORDER BY al.loan_date DESC
LIMIT 20;

-- Most recent location transfers
SELECT 
    at.transaction_date,
    a.asset_code,
    lf.name as from_location,
    lt.name as to_location
FROM asset_transactions at
JOIN assets a ON a.id = at.asset_id
JOIN locations lf ON lf.id = at.from_location_id
JOIN locations lt ON lt.id = at.to_location_id
ORDER BY at.transaction_date DESC
LIMIT 20;
```

## 10. Performance Queries

```sql
-- Check for missing indexes
EXPLAIN SELECT * FROM assets WHERE status = 'dipelihara';
-- Should use: KEY `assets_status_index` (`status`)

-- Assets per category (top categories)
SELECT 
    c.name,
    COUNT(*) as total,
    SUM(IF(a.status = 'aktif', 1, 0)) as available,
    SUM(IF(a.status = 'dipinjam', 1, 0)) as borrowed,
    SUM(IF(a.status = 'dipelihara', 1, 0)) as maintenance,
    SUM(IF(a.status = 'nonaktif', 1, 0)) as retired
FROM assets a
JOIN asset_categories c ON c.id = a.category_id
GROUP BY c.name
ORDER BY total DESC;

-- Most borrowed assets
SELECT 
    a.asset_code,
    a.name,
    COUNT(*) as borrow_count
FROM asset_loans al
JOIN assets a ON a.id = al.asset_id
GROUP BY a.id
ORDER BY borrow_count DESC
LIMIT 10;

-- Most maintained assets
SELECT 
    a.asset_code,
    a.name,
    COUNT(*) as maintenance_count
FROM asset_maintenances am
JOIN assets a ON a.id = am.asset_id
GROUP BY a.id
ORDER BY maintenance_count DESC
LIMIT 10;
```

## Usage in Tinker

```bash
php artisan tinker

# Quick checks
>>> Asset::where('status', 'dipelihara')->count()
>>> Asset::where('status', 'dipinjam')->count()
>>> AssetMaintenance::whereNull('completed_date')->count()
>>> AssetLoan::whereNull('return_date')->count()

# Find specific issue
>>> $asset = Asset::find(9);
>>> $asset->activeMaintenance;  // Should return object or null
>>> $asset->maintenances;  // All maintenance history

# Check relationships
>>> $asset->load('transactions', 'loans', 'maintenances');
>>> $asset->transactions->count()
>>> $asset->loans->count()
>>> $asset->maintenances->count()
```

---

Run these queries when troubleshooting to identify data issues before running application code.
