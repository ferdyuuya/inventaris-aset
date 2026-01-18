# Asset Module - Implementation Guide & Code Reference

## Quick Implementation Checklist

### ✅ Completed Components

- [x] Design Document (`ASSET_MODULE_DESIGN.md`)
- [x] Service Layer
    - [x] `AssetService` - Summary metrics, list, detail
    - [x] `AssetLocationService` - Transfer location actions
    - [x] `AssetBorrowingService` - Borrow/return actions
    - [x] `AssetMaintenanceService` - Maintenance actions
    - [x] `AssetGenerationService` - Asset creation from procurement
- [x] Models
    - [x] `Asset` - Enhanced with all relationships
    - [x] `AssetTransaction` - Location transfer records
    - [x] `AssetLoan` - Borrowing records
    - [x] `AssetMaintenance` - Maintenance records
- [x] Controller
    - [x] `AssetController` - API endpoints for all actions
- [x] Routes
    - [x] `routes/assets.php` - Complete route definitions
- [x] Livewire Components
    - [x] `AssetTableManager` - Paginated list with search/filter
    - [x] `AssetSummaryManager` - Metrics dashboard
    - [x] `AssetDetailManager` - Detail view with actions
- [x] Migration
    - [x] `2026_01_18_000000_add_procurement_id_to_assets.php`

### ⏳ Next Steps (You Must Implement)

#### 1. Register Routes in `routes/web.php`

```php
require __DIR__ . '/assets.php';
```

#### 2. Create Blade Templates

**Location**: `resources/views/assets/`

Required views:

- `summary.blade.php` - Summary page
- `index.blade.php` - Asset list page
- `show.blade.php` - Asset detail page

**Location**: `resources/views/livewire/`

Required components:

- `asset-summary-manager.blade.php` - Summary component
- `asset-table-manager.blade.php` - List component
- `asset-detail-manager.blade.php` - Detail component with tabs & modals

#### 3. Create Policy

**File**: `app/Policies/AssetPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    /**
     * Allow viewing assets only to authenticated users
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view
    }

    /**
     * Allow viewing specific asset only to authenticated users
     */
    public function view(User $user, Asset $asset): bool
    {
        return true; // All authenticated users can view
    }

    /**
     * Allow updating only to authorized users (admin/manager)
     */
    public function update(User $user, Asset $asset): bool
    {
        return $user->is_admin || $user->is_manager;
    }
}
```

#### 4. Register Policy in `AuthServiceProvider`

```php
protected $policies = [
    Asset::class => AssetPolicy::class,
];
```

#### 5. Update Procurement Flow (if not done)

When a procurement is completed:

```php
// In ProcurementController or Observer
use App\Jobs\GenerateAssetsFromProcurement;

// Dispatch job to create assets
GenerateAssetsFromProcurement::dispatch($procurement);
```

#### 6. Create Queue Job

**File**: `app/Jobs/GenerateAssetsFromProcurement.php`

```php
<?php

namespace App\Jobs;

use App\Models\Procurement;
use App\Services\AssetGenerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateAssetsFromProcurement implements ShouldQueue
{
    use Queueable;

    public function __construct(private Procurement $procurement) {}

    public function handle(AssetGenerationService $service): void
    {
        $service->createFromProcurement($this->procurement);
    }
}
```

---

## Database Schema Reference

### assets table

```
id                      BIGINT PK
asset_code              VARCHAR UNIQUE (e.g., "ASSET-LAPT-JAK-0001")
name                    VARCHAR
category_id             BIGINT FK → asset_categories.id
location_id             BIGINT FK → locations.id
supplier_id             BIGINT FK → suppliers.id (NULL)
procurement_id          BIGINT FK → procurements.id (NULL)
purchase_date           DATE
purchase_price          DECIMAL(15,0)
invoice_number          VARCHAR (NULL)
condition               ENUM('baik', 'rusak', 'perlu_perbaikan')
status                  ENUM('aktif', 'dipinjam', 'dipelihara', 'nonaktif')
is_available            BOOLEAN
created_at              TIMESTAMP
updated_at              TIMESTAMP
```

### asset_transactions table

```
id                      BIGINT PK
asset_id                BIGINT FK → assets.id
type                    ENUM('masuk', 'keluar', 'mutasi')
from_location_id        BIGINT FK → locations.id (NULL)
to_location_id          BIGINT FK → locations.id (NULL)
transaction_date        DATE
description             TEXT (reason)
created_by              BIGINT FK → users.id
created_at              TIMESTAMP
updated_at              TIMESTAMP
```

### asset_loans table

```
id                      BIGINT PK
asset_id                BIGINT FK → assets.id
borrower_employee_id    BIGINT FK → employees.id
loan_date               DATE
expected_return_date    DATE (NULL)
return_date             DATE (NULL)
status                  ENUM('dipinjam', 'dikembalikan', 'hilang')
created_at              TIMESTAMP
updated_at              TIMESTAMP
```

### asset_maintenances table

```
id                      BIGINT PK
asset_id                BIGINT FK → assets.id
maintenance_date        DATE
estimated_completion_date DATE (NULL)
completed_date          DATE (NULL)
description             TEXT
status                  ENUM('dalam_proses', 'selesai', 'dibatalkan')
created_by              BIGINT FK → users.id
created_at              TIMESTAMP
updated_at              TIMESTAMP
```

---

## API Endpoints

### GET Endpoints (Read-Only)

```
GET  /assets/summary              # Asset summary metrics
GET  /assets                       # Asset list (paginated)
GET  /assets?search=...           # Search by code/name
GET  /assets?category_id=...      # Filter by category
GET  /assets?status=...           # Filter by status
GET  /assets?location_id=...      # Filter by location
GET  /assets/{id}                 # Asset detail page
```

### POST Endpoints (Controlled Actions)

```
POST /assets/{id}/transfer-location
     Body: { location_id, reason }
     Effect: Update asset location + create transaction

POST /assets/{id}/borrow
     Body: { employee_id, expected_return_date? }
     Effect: Create loan + update asset status

POST /assets/{id}/return
     Effect: Complete loan + update asset status

POST /assets/{id}/send-maintenance
     Body: { reason, estimated_completion_date? }
     Effect: Create maintenance + update asset status

POST /assets/{id}/complete-maintenance
     Effect: Complete maintenance + update asset status
```

---

## Service Methods Reference

### AssetService

```php
// Get metrics
getSummaryMetrics(): array
getAssetList($filters, $perPage, $page)
getAssetDetail($assetId): ?Asset
getAssetHistory($assetId)
getBorrowingHistory($assetId)
getMaintenanceHistory($assetId)
canPerformAction($asset, $action): bool
getAvailableActions($asset): array
invalidateSummaryCache(): void
```

### AssetLocationService

```php
transferAsset($asset, $toLocationId, $reason): AssetTransaction
getLocationHistory($asset)
getCurrentLocation($asset): array
getTransferTimeline($asset)
```

### AssetBorrowingService

```php
borrowAsset($asset, $employeeId, $expectedReturnDate?): AssetLoan
returnAsset($loanId): AssetLoan
markAsLost($loanId): AssetLoan
getCurrentBorrower($asset): ?AssetLoan
hasActiveLoan($asset): bool
getBorrowingHistory($asset)
getOverdueBorrowings()
getEmployeeBorrowingSummary($employeeId): array
```

### AssetMaintenanceService

```php
sendToMaintenance($asset, $reason, $estimatedCompletionDate?): AssetMaintenance
completeMaintenance($maintenanceId): AssetMaintenance
cancelMaintenance($maintenanceId): AssetMaintenance
getCurrentMaintenance($asset): ?AssetMaintenance
hasActiveMaintenance($asset): bool
getMaintenanceHistory($asset)
getOverdueMaintenance()
getMaintenanceSummary(): array
getAverageMaintenanceDuration(): ?string
```

### AssetGenerationService

```php
generateAssetCode($category, $location): string
createFromProcurement($procurement): array
batchCreateFromProcurements($procurementIds): array
isAssetCodeUnique($assetCode): bool
getAssetCodeFormat(): array
```

---

## Model Relationships

### Asset

```php
$asset->category           // BelongsTo AssetCategory
$asset->location           // BelongsTo Location
$asset->supplier           // BelongsTo Supplier
$asset->procurement        // BelongsTo Procurement
$asset->transactions       // HasMany AssetTransaction
$asset->loans              // HasMany AssetLoan
$asset->maintenances       // HasMany AssetMaintenance
$asset->activeLoan()       // Current active loan if any
$asset->activeMaintenance()// Current maintenance if any
```

### AssetTransaction

```php
$transaction->asset        // BelongsTo Asset
$transaction->fromLocation // BelongsTo Location
$transaction->toLocation   // BelongsTo Location
$transaction->creator      // BelongsTo User
```

### AssetLoan

```php
$loan->asset               // BelongsTo Asset
$loan->borrower            // BelongsTo Employee
$loan->isOverdue(): bool   // Check if overdue
$loan->getDaysUntilReturn(): ?int
```

### AssetMaintenance

```php
$maintenance->asset        // BelongsTo Asset
$maintenance->creator      // BelongsTo User
$maintenance->isOverdue(): bool
$maintenance->getDaysSinceStart(): int
$maintenance->getEstimatedDuration(): ?int
$maintenance->getActualDuration(): ?int
```

---

## Livewire Components

### AssetSummaryManager

**Renders**: `asset-summary-manager.blade.php`

**Computed Properties**:

- `metrics` - Summary metrics

**Methods**:

- `refresh()` - Invalidate cache

**Usage**:

```blade
<livewire:asset-summary-manager />
```

### AssetTableManager

**Renders**: `asset-table-manager.blade.php`

**Properties**:

- `perPage: int = 25`
- `search: string`
- `filterCategory: string`
- `filterStatus: string`
- `filterLocation: string`
- `sortField: string = 'created_at'`
- `sortOrder: string = 'desc'`

**Methods**:

- `updatePerPage($perPage)`
- `resetFilters()`
- `toggleSort($field)`

**Computed**:

- `assets` - Paginated asset list
- `categories` - All categories
- `locations` - All locations

**Usage**:

```blade
<livewire:asset-table-manager />
```

### AssetDetailManager

**Renders**: `asset-detail-manager.blade.php`

**Properties**:

- `asset: Asset` - Current asset
- `activeTab: string = 'details'`
- Various form properties for modals

**Methods**:

- `setTab($tab)` - Switch active tab
- `openTransferModal()`, `closeTransferModal()`, `submitTransfer()`
- `openBorrowModal()`, `closeBorrowModal()`, `submitBorrow()`
- `returnAsset()` - Return borrowed asset
- `openMaintenanceModal()`, `closeMaintenanceModal()`, `submitMaintenance()`
- `completeMaintenance()` - Mark maintenance complete

**Computed**:

- `availableActions` - Allowed actions
- `locationHistory` - Location transfer history
- `borrowingHistory` - Borrow history
- `maintenanceHistory` - Maintenance history
- `currentBorrower` - Current loan if any
- `currentMaintenance` - Current maintenance if any
- `employees` - All employees
- `locations` - Available locations

**Usage**:

```blade
<livewire:asset-detail-manager :asset="$asset" />
```

---

## Status Transition Quick Reference

```
aktif → dipinjam (via Borrow)
aktif → dipelihara (via Send to Maintenance)
aktif → nonaktif (via Retire - future)

dipinjam → aktif (via Return)
dipelihara → aktif (via Complete Maintenance)

nonaktif → [no transitions - end state]
```

---

## Example: Using Services Directly

```php
// In a custom command or controller

use App\Models\Asset;
use App\Services\AssetService;
use App\Services\AssetBorrowingService;

$assetService = app(AssetService::class);
$borrowingService = app(AssetBorrowingService::class);

// Get summary
$metrics = $assetService->getSummaryMetrics();

// Get asset details
$asset = Asset::findOrFail(1);
$actions = $assetService->getAvailableActions($asset);

// Borrow an asset
$loan = $borrowingService->borrowAsset($asset, $employeeId, $returnDate);

// Return asset
$borrowingService->returnAsset($loan->id);
```

---

## Flux UI Components to Use

### For Summary Page

- Flux Button
- Flux Card (for metrics display)
- Flux Icon (for status indicators)

### For List Page

- Flux Table
- Flux Input (search)
- Flux Select (filters)
- Flux Pagination
- Flux Button (actions)
- Flux Dropdown

### For Detail Page

- Flux Tabs
- Flux Button
- Flux Modal (for action dialogs)
- Flux Form components
- Flux Input
- Flux Textarea
- Flux Icon (for status badges)

---

## Testing Scenarios

### Scenario 1: Transfer Location

1. Create asset in Location A
2. Call `transferAsset()` to Location B
3. Verify: asset.location_id = B, transaction created
4. Verify: is_available still true, status still 'aktif'

### Scenario 2: Borrow and Return

1. Asset status = 'aktif', is_available = true
2. Call `borrowAsset()` with employee
3. Verify: status = 'dipinjam', is_available = false
4. Call `returnAsset()`
5. Verify: status = 'aktif', is_available = true

### Scenario 3: Send to and Complete Maintenance

1. Asset status = 'aktif', is_available = true
2. Call `sendToMaintenance()` with reason
3. Verify: status = 'dipelihara', is_available = false
4. Call `completeMaintenance()`
5. Verify: status = 'aktif', is_available = true

### Scenario 4: Invalid State Transitions

1. Try to borrow when status = 'dipinjam' → Should fail
2. Try to transfer when is_available = false → Should fail
3. Try to send to maintenance when condition = 'rusak' and status != 'aktif' → Should fail

---

## Troubleshooting

### Assets Not Appearing

- Check: Asset status is not 'nonaktif'
- Check: Asset was created with valid category_id and location_id
- Check: Procurement job completed successfully

### Action Buttons Not Showing

- Check: User has proper permissions (update policy)
- Check: Asset status allows the action
- Check: `getAvailableActions()` returns action in list

### Cache Issues

- Clear cache: `cache()->forget('asset.summary')`
- Or use: `$assetService->invalidateSummaryCache()`

### Pagination Not Working

- Check: Livewire properly initialized with `WithPagination` trait
- Check: perPage value is within reasonable range
- Check: Query returns proper result count

---

**Last Updated**: January 18, 2026  
**Status**: Implementation Ready
