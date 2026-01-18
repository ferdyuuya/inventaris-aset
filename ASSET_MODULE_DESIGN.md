# Asset Management Module - Complete Design & Implementation Guide

**Date**: January 18, 2026  
**System**: Web-Based Asset Management System (Laravel + Livewire + Flux UI)

---

## 1. PAGE FLOW EXPLANATION

### 1.1 Navigation Flow

```
Dashboard/Navigation
    ↓
[Asset Module]
    ├─→ Asset Summary (Overview Page) [READ-ONLY]
    ├─→ Asset List (Table View) [READ-ONLY with Pagination]
    └─→ Asset Detail (Unit Level) [READ-ONLY with Controlled Actions]
```

### 1.2 User Journey

```
1. USER LANDS ON ASSET SUMMARY PAGE
   ├─ Views: Total Assets, Available, Under Maintenance
   ├─ No create/edit/delete buttons
   └─ Click "View All Assets" → Asset List Page

2. USER NAVIGATES TO ASSET LIST PAGE
   ├─ Sees paginated table (10/25/50 per page)
   ├─ Can search by: asset_code, name
   ├─ Can filter by: category, status, location
   ├─ Clicking row → Asset Detail Page
   └─ "New Asset" button → ❌ NOT ALLOWED (must come from procurement)

3. USER OPENS ASSET DETAIL PAGE
   ├─ Tab 1: Details
   │  └─ Displays all read-only asset information
   ├─ Tab 2: History
   │  └─ Shows location changes, borrowing history, maintenance history
   ├─ Tab 3: Borrowing
   │  └─ Current borrow status + borrowing history
   ├─ Tab 4: Maintenance
   │  └─ Maintenance records + status
   └─ Action Buttons:
      ├─ Transfer Location (if status = 'aktif')
      ├─ Borrow Asset (if is_available = true & status = 'aktif')
      └─ Send to Maintenance (if status = 'aktif')
```

---

## 2. BACKEND LOGIC OUTLINE

### 2.1 Controllers Structure

```
app/Http/Controllers/
├── AssetController
│   ├── index()           # Asset List (paginated)
│   ├── show()            # Asset Detail
│   ├── summary()         # Summary metrics
│   └── transferLocation()  # POST action
│   └── borrowAsset()       # POST action
│   └── sendMaintenance()   # POST action
├── AssetTransactionController
│   ├── store()           # Record location change
│   └── index()           # Get transaction history
├── AssetLoanController
│   ├── store()           # Create new loan
│   ├── returnAsset()     # Complete loan
│   └── history()         # Loan history
└── AssetMaintenanceController
    ├── store()           # Send to maintenance
    ├── complete()        # Complete maintenance
    └── history()         # Maintenance history
```

### 2.2 Services Architecture

```
app/Services/
├── AssetService
│   ├── getSummaryMetrics()
│   │   ├─ Total Assets: count(all)
│   │   ├─ Available: count(status='aktif' AND is_available=true)
│   │   └─ Under Maintenance: count(status='dipelihara')
│   │
│   ├── getAssetList($filters, $page)
│   │   ├─ Query with pagination
│   │   ├─ Apply filters (category, status, location)
│   │   └─ Apply search (code, name)
│   │
│   ├── getAssetDetail($assetId)
│   │   ├─ Asset info + relations
│   │   ├─ Current location
│   │   └─ Current borrow/maintenance status
│   │
│   └── canPerformAction($asset, $action)
│       ├─ Validate status
│       ├─ Check permissions
│       └─ Return boolean
│
├── AssetLocationService
│   ├── transferAsset($asset, $toLocation, $reason)
│   │   ├─ Validate location exists
│   │   ├─ Create transaction record
│   │   └─ Update asset.location_id
│   │
│   ├── getLocationHistory($asset)
│   │   └─ Returns all transactions
│   │
│   └── getCurrentLocation($asset)
│       └─ Returns latest location with timestamp
│
├── AssetBorrowingService
│   ├── borrowAsset($asset, $employee, $dueDate)
│   │   ├─ Check if available
│   │   ├─ Create asset_loan record
│   │   └─ Update asset.status = 'dipinjam'
│   │   └─ Update asset.is_available = false
│   │
│   ├── returnAsset($loan)
│   │   ├─ Update loan.status = 'dikembalikan'
│   │   ├─ Update loan.return_date = now()
│   │   ├─ Update asset.status = 'aktif'
│   │   └─ Update asset.is_available = true
│   │
│   ├── getCurrentBorrower($asset)
│   │   └─ Returns active loan record
│   │
│   └── getBorrowingHistory($asset)
│       └─ Returns all loans for asset
│
└── AssetMaintenanceService
    ├── sendToMaintenance($asset, $reason, $estimatedDate)
    │   ├─ Create maintenance record
    │   ├─ Update asset.status = 'dipelihara'
    │   └─ Update asset.is_available = false
    │
    ├── completeMaintenance($maintenance)
    │   ├─ Update maintenance.completed_at
    │   ├─ Update maintenance.status = 'selesai'
    │   ├─ Update asset.status = 'aktif'
    │   └─ Update asset.is_available = true
    │
    └── getMaintenanceHistory($asset)
        └─ Returns all maintenance records
```

### 2.3 Key Business Logic Rules

#### Asset Status Transition Rules

```
┌─────────────────────────────────────────────────────────────┐
│ ASSET STATUS LIFECYCLE                                      │
└─────────────────────────────────────────────────────────────┘

    ┌─────────────────────────────────────────────────────┐
    │                   INITIAL STATE: 'aktif'            │
    │          (Created from procurement, available)      │
    └──────────────────┬──────────────────────────────────┘
                       │
        ┌──────────────┴──────────────┬─────────────────┐
        ↓                             ↓                 ↓
    ┌────────────┐           ┌──────────────┐    ┌──────────────┐
    │ 'dipinjam' │           │ 'dipelihara' │    │  'nonaktif'  │
    │(Borrowed)  │           │(Maintenance) │    │ (Retired)    │
    └────┬───────┘           └──────┬───────┘    └──────────────┘
         │                          │                    ↑
         │ [Return Asset]           │ [Maintenance]      │
         │ is_available=false       │ is_available=false │
         │ Update status='aktif'    │                    │
         │ Update is_available=true │ [Complete Service] │
         │                          │ Update status=     │
         │                          │ 'aktif'            │
         │                          │ is_available=true  │
         │                          └────────────────────┘
         │
         └──────────────────────────────────────────────────→ [Return to 'aktif']

RULES:
- From 'aktif': CAN go to 'dipinjam' OR 'dipelihara' OR 'nonaktif'
- From 'dipinjam': CAN ONLY return to 'aktif' (via returnAsset action)
- From 'dipelihara': CAN ONLY return to 'aktif' (via completeMaintenance action)
- From 'nonaktif': ❌ NO ACTIONS ALLOWED (end of lifecycle)
```

#### is_available Flag Rules

```
is_available = true
├─ Precondition: status = 'aktif'
├─ Allows: Borrowing, Transfer Location, Send to Maintenance
└─ When: Asset is in its normal location and not currently used

is_available = false
├─ Precondition: status = 'dipinjam' OR status = 'dipelihara'
├─ Blocks: All actions except return/complete operations
└─ When: Asset is borrowed or being maintained
```

---

## 3. DATABASE TABLES & RELATIONSHIPS

### 3.1 Core Tables (Already Exist)

#### `assets` Table

```sql
id                  BIGINT PRIMARY KEY
asset_code          VARCHAR UNIQUE (auto-generated from service)
name                VARCHAR (from procurement.name)
category_id         BIGINT FOREIGN KEY → asset_categories.id
location_id         BIGINT FOREIGN KEY → locations.id
purchase_date       DATE (from procurement.procurement_date)
purchase_price      DECIMAL (from procurement.unit_price)
invoice_number      VARCHAR (from procurement.invoice_number)
supplier_id         BIGINT FOREIGN KEY → suppliers.id (NULLABLE)
condition           ENUM: 'baik', 'rusak', 'perlu_perbaikan' DEFAULT 'baik'
is_available        BOOLEAN DEFAULT true
status              ENUM: 'aktif', 'dipinjam', 'dipelihara', 'nonaktif' DEFAULT 'aktif'
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### `asset_transactions` Table

```sql
id                  BIGINT PRIMARY KEY
asset_id            BIGINT FOREIGN KEY → assets.id
type                ENUM: 'masuk', 'keluar', 'mutasi' (mutasi = transfer)
from_location_id    BIGINT FOREIGN KEY → locations.id (NULLABLE)
to_location_id      BIGINT FOREIGN KEY → locations.id (NULLABLE)
transaction_date    DATE
description         TEXT (reason for transfer)
created_by          BIGINT FOREIGN KEY → users.id
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### `asset_loans` Table

```sql
id                          BIGINT PRIMARY KEY
asset_id                    BIGINT FOREIGN KEY → assets.id
borrower_employee_id        BIGINT FOREIGN KEY → employees.id
loan_date                   DATE
expected_return_date        DATE (NULLABLE)
return_date                 DATE (NULLABLE)
status                      ENUM: 'dipinjam', 'dikembalikan', 'hilang'
created_at                  TIMESTAMP
updated_at                  TIMESTAMP
```

#### `asset_maintenances` Table

```sql
id                          BIGINT PRIMARY KEY
asset_id                    BIGINT FOREIGN KEY → assets.id
maintenance_date            DATE
estimated_completion_date   DATE (NULLABLE)
completed_date              DATE (NULLABLE)
description                 TEXT (reason for maintenance)
status                      ENUM: 'dalam_proses', 'selesai', 'dibatalkan'
created_by                  BIGINT FOREIGN KEY → users.id
created_at                  TIMESTAMP
updated_at                  TIMESTAMP
```

### 3.2 Relationships Diagram

```
┌────────────────────┐
│      Asset         │
├────────────────────┤
│ id (PK)           │
│ asset_code        │─┐ UNIQUE
│ name              │ │
│ category_id (FK)  │─┼──→ AssetCategory
│ location_id (FK)  │─┼──→ Location
│ purchase_date     │ │
│ purchase_price    │ │
│ invoice_number    │ │
│ supplier_id (FK)  │─┼──→ Supplier
│ condition         │ │
│ is_available      │ │
│ status            │ │
│ created_at        │ │
│ updated_at        │ │
└────────────────────┘ │
       │               │
       ├──→ AssetTransaction (1:N)
       ├──→ AssetLoan (1:N)
       └──→ AssetMaintenance (1:N)

Note: Asset is generated ONLY from Procurement
      No direct foreign key, but linked via:
      - Same asset_code pattern
      - Same category_id
      - Same location_id (initial)
      - Same invoice_number
```

### 3.3 Required Migration: Add procurement_id to assets

```sql
ALTER TABLE assets ADD COLUMN procurement_id BIGINT UNSIGNED NULLABLE AFTER id;
ALTER TABLE assets ADD FOREIGN KEY (procurement_id) REFERENCES procurements(id) ON DELETE CASCADE;
```

---

## 4. ASSET STATUS TRANSITION RULES (DETAILED)

### 4.1 State Machine Definition

```
State: 'aktif' (Active)
├─ Meaning: Asset is in its designated location, ready to use
├─ is_available: true
├─ Valid Transitions:
│  ├─ TO 'dipinjam' via borrowAsset() action
│  ├─ TO 'dipelihara' via sendMaintenance() action
│  └─ TO 'nonaktif' via retireAsset() action (future)
└─ Allowed Actions:
   ├─ Transfer Location (location change only)
   ├─ Borrow Asset
   └─ Send to Maintenance

State: 'dipinjam' (Borrowed)
├─ Meaning: Asset is currently borrowed by an employee
├─ is_available: false
├─ Valid Transitions:
│  └─ TO 'aktif' via returnAsset() action [ONLY]
└─ Allowed Actions:
   └─ Return Asset [ONLY]

State: 'dipelihara' (Under Maintenance)
├─ Meaning: Asset is being serviced/repaired
├─ is_available: false
├─ Valid Transitions:
│  └─ TO 'aktif' via completeMaintenance() action [ONLY]
└─ Allowed Actions:
   └─ Complete Maintenance [ONLY]

State: 'nonaktif' (Inactive/Retired)
├─ Meaning: Asset is no longer in service
├─ is_available: false
├─ Valid Transitions: NONE
└─ Allowed Actions: NONE (end of lifecycle)
```

### 4.2 Condition Enum Rules

```
condition = 'baik' (Good)
├─ Asset functions normally
├─ All actions available
└─ Can be borrowed, transferred, maintained

condition = 'rusak' (Damaged/Broken)
├─ Asset has significant damage
├─ CAN: Transfer, Send to Maintenance, Retire
├─ CANNOT: Borrow (except for maintenance return)
└─ Should be sent to maintenance immediately

condition = 'perlu_perbaikan' (Needs Repair)
├─ Asset has minor issues but still functional
├─ CAN: All actions
├─ RECOMMEND: Send to maintenance soon
└─ Borrowing allowed (take at own risk)
```

### 4.3 Action Validation Matrix

```
┌──────────────────┬────────┬──────────┬──────────────┬───────────┐
│ Action           │ Status │ Available│ Condition    │ Allowed   │
├──────────────────┼────────┼──────────┼──────────────┼───────────┤
│ Transfer         │ aktif  │ true     │ any          │ ✅ YES    │
│ Location         │ dipinjam│ false    │ any          │ ❌ NO     │
│                  │ dipelihara│ false  │ any          │ ❌ NO     │
│                  │ nonaktif│ false    │ any          │ ❌ NO     │
├──────────────────┼────────┼──────────┼──────────────┼───────────┤
│ Borrow Asset     │ aktif  │ true     │ baik/perlu.. │ ✅ YES    │
│                  │ aktif  │ true     │ rusak        │ ❌ NO     │
│                  │ dipinjam│ false    │ any          │ ❌ NO     │
│                  │ dipelihara│ false  │ any          │ ❌ NO     │
│                  │ nonaktif│ false    │ any          │ ❌ NO     │
├──────────────────┼────────┼──────────┼──────────────┼───────────┤
│ Send to          │ aktif  │ true     │ any          │ ✅ YES    │
│ Maintenance      │ dipinjam│ false    │ any          │ ❌ NO     │
│                  │ dipelihara│ false  │ any          │ ❌ NO     │
│                  │ nonaktif│ false    │ any          │ ❌ NO     │
├──────────────────┼────────┼──────────┼──────────────┼───────────┤
│ Return Asset     │ dipinjam│ false    │ any          │ ✅ YES    │
│ (from borrow)    │ aktif  │ true     │ any          │ ❌ NO     │
│                  │ dipelihara│ false  │ any          │ ❌ NO     │
│                  │ nonaktif│ false    │ any          │ ❌ NO     │
├──────────────────┼────────┼──────────┼──────────────┼───────────┤
│ Complete         │ dipelihara│ false  │ any          │ ✅ YES    │
│ Maintenance      │ aktif  │ true     │ any          │ ❌ NO     │
│                  │ dipinjam│ false    │ any          │ ❌ NO     │
│                  │ nonaktif│ false    │ any          │ ❌ NO     │
└──────────────────┴────────┴──────────┴──────────────┴───────────┘
```

---

## 5. AUTHORIZATION & VALIDATION RULES

### 5.1 User Permissions

```
Role: Admin
├─ Can view all assets
├─ Can perform all actions
├─ Can access full history
└─ Can view reports

Role: Manager/Supervisor
├─ Can view all assets
├─ Can perform: Transfer Location, Send to Maintenance
├─ CAN borrow assets
├─ Can view history (filtered by location/department)
└─ CANNOT: Direct asset editing

Role: Employee
├─ Can view assets in their location
├─ Can borrow assets (with approval)
├─ Can view their own borrowing history
└─ CANNOT: Transfer, Maintenance actions

Role: Guest/Unauthenticated
├─ CANNOT view any assets
└─ Redirected to login
```

### 5.2 Validation Rules

```
CREATE ASSET (via AssetGenerationService)
├─ ONLY from Procurement.create() via queue job
├─ Asset code must be unique
├─ Category must exist
├─ Location must exist
├─ Purchase date cannot be in future
├─ Purchase price must be > 0
├─ Name must not be empty
└─ Status always defaults to 'aktif'

TRANSFER LOCATION
├─ Asset status must be 'aktif'
├─ Asset must be available (is_available = true)
├─ To-location must exist
├─ From-location must not equal to-location
├─ Description (reason) is required
├─ User must have permission
└─ Creates asset_transaction record

BORROW ASSET
├─ Asset status must be 'aktif'
├─ Asset must be available (is_available = true)
├─ Condition must be 'baik' or 'perlu_perbaikan'
├─ Borrower (employee) must exist
├─ Expected return date must be >= today
├─ Only ONE active loan per asset
├─ User must have permission
└─ Updates: status='dipinjam', is_available=false

RETURN ASSET (from Borrow)
├─ Must have active loan (status='dipinjam')
├─ Actual return date = today
├─ Updates: status='aktif', is_available=true
└─ Loan record marked as 'dikembalikan'

SEND TO MAINTENANCE
├─ Asset status must be 'aktif'
├─ Asset must be available (is_available = true)
├─ Description (reason) is required
├─ Estimated completion date must be >= today
├─ Only ONE active maintenance record
├─ User must have permission
└─ Updates: status='dipelihara', is_available=false

COMPLETE MAINTENANCE
├─ Must have active maintenance record
├─ Completed date = today
├─ Updates: status='aktif', is_available=true
└─ Maintenance record marked as 'selesai'

EDIT ASSET (Direct Manual Editing)
├─ ❌ STRICTLY FORBIDDEN
├─ Exception: Admin can update condition enum only (if damaged discovered)
├─ All other fields are READ-ONLY
└─ History logging required for condition changes
```

### 5.3 Data Integrity Rules

```
Asset Creation
├─ MUST originate from Procurement record
├─ CANNOT be manually created via UI
├─ Linked via procurement_id foreign key
└─ Immutable fields: asset_code, category, purchase_price, supplier

Asset Updates
├─ location_id: Updated only via Transfer Location action
├─ status: Updated only via state transition actions
├─ is_available: Derived from status (NEVER edited directly)
├─ condition: Only updatable by authorized users (with logging)
├─ purchase_price: IMMUTABLE (from procurement)
├─ purchase_date: IMMUTABLE (from procurement)
└─ name: IMMUTABLE (from procurement)

Audit Trail
├─ All state transitions logged
├─ asset_transactions records all movements
├─ asset_loans records all borrowing
├─ asset_maintenances records all service
└─ created_by/updated_by tracked everywhere
```

---

## 6. PAGINATION STRATEGY FOR ASSET LIST

### 6.1 Implementation Approach

```
Framework: Laravel Pagination (Built-in)
Frontend: Livewire with Flux Pagination Component
Database: Indexed queries

Pagination Parameters
├─ Default page size: 25 items per page
├─ Options: [10, 25, 50, 100]
├─ Total count: Cached for 5 minutes
├─ Current page: Via URL query parameter (?page=2)
└─ Sort fields: asset_code, name, purchase_date, location
```

### 6.2 Query Optimization

```
SELECT
    a.id,
    a.asset_code,
    a.name,
    a.purchase_price,
    a.purchase_date,
    a.status,
    a.is_available,
    c.name AS category_name,
    l.name AS location_name
FROM assets a
    LEFT JOIN asset_categories c ON a.category_id = c.id
    LEFT JOIN locations l ON a.location_id = l.id
WHERE (search conditions)
ORDER BY a.created_at DESC
LIMIT {page_size}
OFFSET {(page - 1) * page_size}

Indexes Required:
├─ PRIMARY: id
├─ UNIQUE: asset_code
├─ INDEX: category_id (for filter)
├─ INDEX: location_id (for filter)
├─ INDEX: status (for filter)
├─ INDEX: is_available
├─ INDEX: created_at (for sorting)
└─ COMPOSITE INDEX: (status, is_available, created_at)
```

### 6.3 Frontend Pagination Components

```
1. PAGINATION CONTROLS (Bottom of table)
   ├─ "Previous" button
   ├─ Page numbers (1, 2, 3, ..., Last)
   ├─ "Next" button
   └─ "Showing X-Y of Z items"

2. PER-PAGE SELECTOR
   ├─ Dropdown: 10 | 25 | 50 | 100
   └─ Updates current page to 1

3. LAZY LOADING (Optional)
   ├─ Load next page in background
   └─ Smooth transition

4. CACHING STRATEGY
   ├─ Cache summary metrics for 5 minutes
   ├─ Cache paginated results for 2 minutes
   ├─ Invalidate on asset creation/modification
   └─ Use Redis for production
```

### 6.4 Livewire Implementation Pattern

```php
class AssetTable extends Component
{
    public int $perPage = 25;
    public int $page = 1;
    public string $search = '';
    public string $filterCategory = '';
    public string $filterStatus = '';
    public string $filterLocation = '';
    public string $sortField = 'created_at';
    public string $sortOrder = 'desc';

    #[Computed]
    public function assets()
    {
        return Asset::query()
            ->when($this->search, fn($q) =>
                $q->where('asset_code', 'like', "%{$this->search}%")
                  ->orWhere('name', 'like', "%{$this->search}%")
            )
            ->when($this->filterCategory, fn($q) =>
                $q->where('category_id', $this->filterCategory)
            )
            ->when($this->filterStatus, fn($q) =>
                $q->where('status', $this->filterStatus)
            )
            ->when($this->filterLocation, fn($q) =>
                $q->where('location_id', $this->filterLocation)
            )
            ->with(['category', 'location'])
            ->orderBy($this->sortField, $this->sortOrder)
            ->paginate($this->perPage, page: $this->page);
    }

    public function updatePerPage(int $perPage): void
    {
        $this->perPage = $perPage;
        $this->page = 1; // Reset to first page
    }

    public function search(string $query): void
    {
        $this->search = $query;
        $this->page = 1; // Reset to first page
    }

    public function render()
    {
        return view('livewire.asset-table', [
            'assets' => $this->assets,
        ]);
    }
}
```

---

## 7. FILE GENERATION SYSTEM (Asset Creation from Procurement)

### 7.1 Asset Code Generation

```
Format: ASSET-XXXX-YYY-ZZZZ
├─ Prefix: "ASSET"
├─ XXXX: Asset category code (first 4 chars of category name)
├─ YYY: Location code (first 3 chars of location name)
├─ ZZZZ: Sequential number (padded with zeros)

Examples:
├─ Asset from "Laptop" category in "Jakarta":
│  └─ ASSET-LAPT-JAK-0001
│
├─ Asset from "Office Chair" category in "Surabaya":
│  └─ ASSET-OFFI-SUR-0027
│
└─ Asset from "Printer" category in "Bandung":
   └─ ASSET-PRIN-BAN-0003

Service: AssetGenerationService
├─ generateAssetCode($category, $location): string
├─ createFromProcurement($procurement): Asset
├─ batch($procurements): Collection<Asset>
└─ Ensures: uniqueness, consistency, sequencing
```

### 7.2 Asset Generation Process (Queue Job)

```
TRIGGER: Procurement created/completed

FLOW:
1. ProcurementController::store() creates procurement
2. Fires event: ProcurementCreated
3. Queue job: GenerateAssetsFromProcurement
   ├─ Get procurement record
   ├─ FOR EACH quantity:
   │  ├─ Generate unique asset_code
   │  ├─ Create Asset record with:
   │  │  ├─ asset_code
   │  │  ├─ name (from procurement.name)
   │  │  ├─ category_id (from procurement.asset_category_id)
   │  │  ├─ location_id (from procurement.location_id)
   │  │  ├─ purchase_date (from procurement.procurement_date)
   │  │  ├─ purchase_price (from procurement.unit_price)
   │  │  ├─ invoice_number (from procurement.invoice_number)
   │  │  ├─ supplier_id (from procurement.supplier_id)
   │  │  ├─ status (default: 'aktif')
   │  │  ├─ is_available (default: true)
   │  │  ├─ condition (default: 'baik')
   │  │  └─ procurement_id (link back)
   │  │
   │  ├─ Log: AssetCreated event
   │  └─ Send notification to manager
   │
4. Complete job
5. Update procurement.status = 'aset_dibuat'

IDEMPOTENCY: If asset_code exists, skip (prevents duplicates)
```

---

## 8. SUMMARY METRICS CALCULATION

### 8.1 Metrics Definition

```
METRIC 1: Total Assets
├─ Query: COUNT(*) FROM assets WHERE status != 'nonaktif'
├─ Excludes: Retired assets
├─ Usage: Dashboard overview
└─ Cache: 5 minutes

METRIC 2: Available Assets
├─ Query: COUNT(*) FROM assets
│         WHERE status = 'aktif' AND is_available = true
├─ Meaning: Ready to use, not borrowed/maintained
├─ Usage: Inventory health check
└─ Cache: 5 minutes

METRIC 3: Under Maintenance
├─ Query: COUNT(*) FROM assets WHERE status = 'dipelihara'
├─ Includes: All assets currently being serviced
├─ Usage: Service visibility
└─ Cache: 5 minutes

METRIC 4: Currently Borrowed (Optional)
├─ Query: COUNT(*) FROM assets WHERE status = 'dipinjam'
├─ Shows: Active loans
└─ Usage: Asset tracking

METRIC 5: By Category (Breakdown)
├─ Query: GROUP BY category_id, COUNT(*)
├─ Shows: Distribution across categories
└─ Usage: Category-level inventory view

METRIC 6: By Location (Breakdown)
├─ Query: GROUP BY location_id, COUNT(*)
├─ Shows: Distribution across locations
└─ Usage: Location-level inventory view
```

### 8.2 Service Implementation

```php
class AssetSummaryService
{
    public function getSummary(): array
    {
        return cache()->remember('asset.summary', 300, function () {
            return [
                'total_assets' => Asset::where('status', '!=', 'nonaktif')->count(),
                'available_assets' => Asset::where('status', 'aktif')
                    ->where('is_available', true)
                    ->count(),
                'under_maintenance' => Asset::where('status', 'dipelihara')->count(),
                'currently_borrowed' => Asset::where('status', 'dipinjam')->count(),
                'by_category' => Asset::select('category_id')
                    ->withCount('*')
                    ->groupBy('category_id')
                    ->with('category')
                    ->get(),
                'by_location' => Asset::select('location_id')
                    ->withCount('*')
                    ->groupBy('location_id')
                    ->with('location')
                    ->get(),
            ];
        });
    }

    public function invalidateSummary(): void
    {
        cache()->forget('asset.summary');
    }
}
```

---

## 9. NEXT STEPS FOR IMPLEMENTATION

### Phase 1: Database & Models

- [ ] Add migration: `procurement_id` to assets table
- [ ] Update Asset model with relations
- [ ] Create/update all related models with relationships
- [ ] Create asset_maintenances migration (if missing)

### Phase 2: Services

- [ ] AssetService (summary, list, detail)
- [ ] AssetLocationService (transfer logic)
- [ ] AssetBorrowingService (borrow/return logic)
- [ ] AssetMaintenanceService (maintenance logic)
- [ ] AssetGenerationService (auto-create from procurement)

### Phase 3: Controllers

- [ ] AssetController (index, show, summary)
- [ ] API endpoints for actions (transfer, borrow, return, maintenance)

### Phase 4: Frontend Components (Livewire + Flux)

- [ ] Asset Summary component
- [ ] Asset Table component (with pagination, search, filter)
- [ ] Asset Detail component (with tabs)
- [ ] Action modals (transfer, borrow, maintenance)

### Phase 5: Queue Jobs

- [ ] GenerateAssetsFromProcurement job
- [ ] Event listeners and notifications

### Phase 6: Testing & Validation

- [ ] Unit tests for services
- [ ] Feature tests for controllers
- [ ] Form validation tests
- [ ] State transition tests

---

## 10. KEY DESIGN DECISIONS RATIONALE

| Decision                         | Rationale                                                                                         |
| -------------------------------- | ------------------------------------------------------------------------------------------------- |
| **Assets ONLY from Procurement** | Ensures controlled inventory, prevents manual errors, maintains procurement-to-asset traceability |
| **Read-Only Asset Fields**       | Locks critical data, prevents accidental modifications, maintains data integrity                  |
| **Status + is_available Flags**  | Dual flags provide clear state machine, prevents invalid transitions                              |
| **Separate Transaction Tables**  | Maintains audit trail, enables detailed history reports, supports reversible actions              |
| **Queue Job for Asset Creation** | Async processing handles bulk procurement, prevents page timeout, decouples operations            |
| **Role-Based Permissions**       | Ensures only authorized users perform actions, maintains security                                 |
| **Summary Caching**              | Improves dashboard performance, reduces database load, acceptable 5-minute staleness              |
| **Pagination Required**          | Handles large inventory gracefully, improves UI performance, standard UX pattern                  |

---

**END OF DESIGN DOCUMENT**
