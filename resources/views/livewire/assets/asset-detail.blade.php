<div class="space-y-6">
    {{-- ============================================== --}}
    {{-- ASSET DETAIL PAGE - UI ONLY (DUMMY DATA)      --}}
    {{-- Backend logic will be reintroduced later      --}}
    {{-- ============================================== --}}

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column (2/3 width on large screens) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- ============================== --}}
            {{-- A. Asset Basic Information     --}}
            {{-- ============================== --}}
            <flux:card>
                <flux:heading size="lg">Asset Information</flux:heading>
                <flux:text class="mt-2">Basic details about this asset</flux:text>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    {{-- Left Column --}}
                    <div class="space-y-4">
                        <div class="flex justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-500">Asset Code</flux:text>
                            <flux:text class="font-mono font-medium">{{ $asset->asset_code }}</flux:text>
                        </div>
                        <div class="flex justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-500">Asset Name</flux:text>
                            <flux:text class="font-medium">{{ $asset->name }}</flux:text>
                        </div>
                        <div class="flex justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-500">Category</flux:text>
                            <flux:badge color="zinc">{{ $asset->category->name ?? 'N/A' }}</flux:badge>
                        </div>
                    </div>

                    {{-- Right Column --}}
                    <div class="space-y-4">
                        <div class="flex justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-500">Purchase Price</flux:text>
                            <flux:text class="font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($asset->purchase_price, 0, ',', '.') }}</flux:text>
                        </div>
                        <div class="flex justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-500">Procurement Date</flux:text>
                            <flux:text class="font-medium">{{ $asset->purchase_date?->format('d M Y') ?? 'N/A' }}</flux:text>
                        </div>
                        <div class="flex justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-500">Condition</flux:text>
                            @switch($asset->condition)
                                @case('baik')
                                    <flux:badge color="green">Good</flux:badge>
                                    @break
                                @case('rusak')
                                    <flux:badge color="red">Damaged</flux:badge>
                                    @break
                                @case('perlu_perbaikan')
                                    <flux:badge color="yellow">Needs Repair</flux:badge>
                                    @break
                                @default
                                    <flux:badge color="zinc">Unknown</flux:badge>
                            @endswitch
                        </div>
                    </div>
                </div>
            </flux:card>

            {{-- ============================== --}}
            {{-- C. Asset History Log           --}}
            {{-- (Location Transfer History)    --}}
            {{-- ============================== --}}
            <flux:card>
                <flux:heading size="lg">Location History</flux:heading>
                <flux:text class="mt-2">Asset transfer history between locations</flux:text>

                <div class="flow-root mt-6">
                    <ul role="list" class="-mb-8">
                        {{-- History Entry 1 --}}
                        <li>
                            <div class="relative pb-8">
                                <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-zinc-200 dark:bg-zinc-700" aria-hidden="true"></span>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-4 ring-white dark:ring-zinc-900">
                                            <flux:icon.arrows-right-left class="size-4 text-white" />
                                        </span>
                                    </div>
                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                        <div>
                                            <flux:text>
                                                Transferred from <span class="font-medium">Warehouse A</span> to <span class="font-medium">IT Department - Floor 3</span>
                                            </flux:text>
                                            <flux:text size="sm" class="text-zinc-500 mt-1">
                                                Performed by: John Admin
                                            </flux:text>
                                        </div>
                                        <flux:text size="sm" class="text-zinc-500 whitespace-nowrap">
                                            10 Jan 2026
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                        </li>

                        {{-- History Entry 2 --}}
                        <li>
                            <div class="relative pb-8">
                                {{-- <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-zinc-200 dark:bg-zinc-700" aria-hidden="true"></span> --}}
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-4 ring-white dark:ring-zinc-900">
                                            <flux:icon.arrows-right-left class="size-4 text-white" />
                                        </span>
                                    </div>
                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                        <div>
                                            <flux:text>
                                                Transferred from <span class="font-medium">Main Office</span> to <span class="font-medium">Warehouse A</span>
                                            </flux:text>
                                            <flux:text size="sm" class="text-zinc-500 mt-1">
                                                Performed by: Sarah Manager
                                            </flux:text>
                                        </div>
                                        <flux:text size="sm" class="text-zinc-500 whitespace-nowrap">
                                            05 Dec 2025
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </flux:card>

            {{-- ============================== --}}
            {{-- D. Borrowing History           --}}
            {{-- ============================== --}}
            <flux:card>
                <flux:heading size="lg">Borrowing History</flux:heading>
                <flux:text class="mt-2">Record of asset loans to employees</flux:text>

                <div class="mt-6">

                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Borrower</flux:table.column>
                        <flux:table.column>Department</flux:table.column>
                        <flux:table.column>Loan Date</flux:table.column>
                        <flux:table.column>Return Date</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        <flux:table.row>
                            <flux:table.cell class="font-medium">Michael Chen</flux:table.cell>
                            <flux:table.cell>Software Development</flux:table.cell>
                            <flux:table.cell>12 Jan 2026</flux:table.cell>
                            <flux:table.cell>18 Jan 2026</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="green">Returned</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                        <flux:table.row>
                            <flux:table.cell class="font-medium">Emily Rodriguez</flux:table.cell>
                            <flux:table.cell>Marketing</flux:table.cell>
                            <flux:table.cell>01 Dec 2025</flux:table.cell>
                            <flux:table.cell>10 Dec 2025</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="green">Returned</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                        <flux:table.row>
                            <flux:table.cell class="font-medium">David Park</flux:table.cell>
                            <flux:table.cell>Finance</flux:table.cell>
                            <flux:table.cell>15 Oct 2025</flux:table.cell>
                            <flux:table.cell>25 Oct 2025</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="green">Returned</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                </flux:table>
                </div>
            </flux:card>
        </div>

        {{-- Right Column (1/3 width on large screens) --}}
        <div class="space-y-6">

            {{-- ============================== --}}
            {{-- B. Current Asset State         --}}
            {{-- ============================== --}}
            <flux:card>
                <flux:heading size="lg">Current Status</flux:heading>

                <div class="space-y-4 mt-4">
                    {{-- Status --}}
                    <div class="flex items-center justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:text class="text-zinc-500">Status</flux:text>
                        @switch($asset->status)
                            @case('aktif')
                                <flux:badge color="green" size="lg">Active</flux:badge>
                                @break
                            @case('dipinjam')
                                <flux:badge color="purple" size="lg">Borrowed</flux:badge>
                                @break
                            @case('dipelihara')
                                <flux:badge color="yellow" size="lg">Maintenance</flux:badge>
                                @break
                            @case('nonaktif')
                                <flux:badge color="red" size="lg">Inactive</flux:badge>
                                @break
                            @default
                                <flux:badge color="zinc" size="lg">Unknown</flux:badge>
                        @endswitch
                    </div>

                    {{-- Current Location --}}
                    <div class="flex items-center justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:text class="text-zinc-500">Location</flux:text>
                        <div class="flex items-center gap-2">
                            <flux:icon.map-pin class="size-4 text-zinc-400" />
                            <flux:text class="font-medium">{{ $asset->location->name ?? 'N/A' }}</flux:text>
                        </div>
                    </div>

                    {{-- Availability --}}
                    <div class="flex items-center justify-between py-3">
                        <flux:text class="text-zinc-500">Available for Loan</flux:text>
                        @if($asset->is_available)
                            <flux:badge color="green">Yes</flux:badge>
                        @else
                            <flux:badge color="red">No</flux:badge>
                        @endif
                    </div>
                </div>
            </flux:card>

            {{-- Current Borrower Card (Shown when borrowed) --}}
            {{-- Example: Uncomment to see borrowed state --}}
            {{--
            <flux:card class="space-y-4 border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20">
                <div class="flex items-center gap-2">
                    <flux:icon.arrow-right-circle class="size-5 text-purple-500" />
                    <flux:heading size="lg">Currently Borrowed</flux:heading>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Borrower</flux:text>
                        <flux:text class="font-medium">Michael Chen</flux:text>
                    </div>
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Department</flux:text>
                        <flux:text>Software Development</flux:text>
                    </div>
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Since</flux:text>
                        <flux:text>12 Jan 2026</flux:text>
                    </div>
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Expected Return</flux:text>
                        <flux:text>20 Jan 2026</flux:text>
                    </div>
                </div>

                <flux:button variant="primary" class="w-full" icon="arrow-uturn-left">
                    Return Asset
                </flux:button>
            </flux:card>
            --}}

            {{-- Maintenance Alert Card (Shown when under maintenance) --}}
            {{-- Example: Uncomment to see maintenance state --}}
            {{--
            <flux:card class="space-y-4 border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/20">
                <div class="flex items-center gap-2">
                    <flux:icon.wrench-screwdriver class="size-5 text-yellow-500" />
                    <flux:heading size="lg">Under Maintenance</flux:heading>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Started</flux:text>
                        <flux:text>15 Jan 2026</flux:text>
                    </div>
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Est. Completion</flux:text>
                        <flux:text>22 Jan 2026</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-zinc-500">Reason</flux:text>
                        <flux:text class="mt-1">Battery replacement and system upgrade</flux:text>
                    </div>
                </div>

                <flux:button variant="primary" class="w-full" icon="check-circle">
                    Complete Maintenance
                </flux:button>
            </flux:card>
            --}}

            {{-- Quick Stats Card --}}
            <flux:card>
                <flux:heading size="lg">Quick Stats</flux:heading>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <flux:heading size="xl" class="text-blue-600 dark:text-blue-400">3</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">Total Loans</flux:text>
                    </div>
                    <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <flux:heading size="xl" class="text-green-600 dark:text-green-400">2</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">Transfers</flux:text>
                    </div>
                    <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <flux:heading size="xl" class="text-yellow-600 dark:text-yellow-400">1</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">Maintenance</flux:text>
                    </div>
                    <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <flux:heading size="xl" class="text-zinc-600 dark:text-zinc-400">2y</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">Asset Age</flux:text>
                    </div>
                </div>
            </flux:card>

            {{-- Maintenance History Card --}}
            <flux:card>
                <flux:heading size="lg">Maintenance History</flux:heading>

                <div class="space-y-3 mt-4">
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <flux:text class="font-medium">Screen Repair</flux:text>
                            <flux:badge color="green" size="sm">Completed</flux:badge>
                        </div>
                        <flux:text size="sm" class="text-zinc-500">Aug 2025 • 5 days</flux:text>
                    </div>
                </div>

                {{-- Empty State (alternative) --}}
                {{--
                <div class="text-center py-6">
                    <flux:icon.wrench-screwdriver class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                    <flux:text class="mt-2 text-zinc-500">No maintenance history</flux:text>
                </div>
                --}}
            </flux:card>
        </div>
    </div>
</div>
