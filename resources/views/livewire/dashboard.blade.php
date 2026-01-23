<div class="space-y-6">
    {{-- ============================================== --}}
    {{-- SECTION 1: SYSTEM STATISTICS --}}
    {{-- ============================================== --}}
    <div>
        <flux:heading size="lg" class="mb-4">System Overview</flux:heading>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            {{-- Total Assets --}}
            <div class="p-4 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-zinc-100 dark:bg-zinc-700 rounded-lg">
                        <flux:icon.cube class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($totalAssets) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Total Assets</p>
                    </div>
                </div>
            </div>

            {{-- Active Assets --}}
            <div class="p-4 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($activeAssets) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Active</p>
                    </div>
                </div>
            </div>

            {{-- Borrowed Assets --}}
            <div class="p-4 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <flux:icon.arrow-right-circle class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($borrowedAssetsCount) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Borrowed</p>
                    </div>
                </div>
            </div>

            {{-- Under Maintenance --}}
            <div class="p-4 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                        <flux:icon.wrench-screwdriver class="size-5 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($maintenanceAssets) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Maintenance</p>
                    </div>
                </div>
            </div>

            {{-- Disposed Assets --}}
            <div class="p-4 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <flux:icon.archive-box-x-mark class="size-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($disposedAssets) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Disposed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <flux:separator />

    {{-- ============================================== --}}
    {{-- SECTION 2: ONGOING MAINTENANCE (PRIMARY FOCUS) --}}
    {{-- ============================================== --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <flux:icon.wrench-screwdriver class="size-5 text-yellow-600 dark:text-yellow-400" />
                <flux:heading size="lg">Ongoing Maintenance</flux:heading>
                @if($ongoingMaintenance->count() > 0)
                    <flux:badge color="yellow">{{ $ongoingMaintenance->count() }} active</flux:badge>
                @endif
            </div>
            <a href="{{ route('maintenance.assets.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                View all
            </a>
        </div>

        @if($ongoingMaintenance->count() > 0)
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Asset Code</flux:table.column>
                        <flux:table.column>Asset Name</flux:table.column>
                        <flux:table.column>Start Date</flux:table.column>
                        <flux:table.column>Est. Completion</flux:table.column>
                        <flux:table.column>Location</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($ongoingMaintenance as $maintenance)
                            <flux:table.row>
                                <flux:table.cell class="font-mono text-sm">
                                    <a href="{{ route('assets.show', $maintenance->asset) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $maintenance->asset->asset_code }}
                                    </a>
                                </flux:table.cell>
                                <flux:table.cell>{{ $maintenance->asset->name }}</flux:table.cell>
                                <flux:table.cell>{{ $maintenance->maintenance_date?->format('d M Y') ?? 'N/A' }}</flux:table.cell>
                                <flux:table.cell>
                                    @if($maintenance->estimated_completion_date)
                                        <span class="{{ $maintenance->estimated_completion_date->isPast() ? 'text-red-600 dark:text-red-400 font-medium' : '' }}">
                                            {{ $maintenance->estimated_completion_date->format('d M Y') }}
                                            @if($maintenance->estimated_completion_date->isPast())
                                                <flux:badge color="red" size="sm" class="ml-1">Overdue</flux:badge>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-zinc-400">Not set</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>{{ $maintenance->asset->location->name ?? 'N/A' }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge color="yellow">In Progress</flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        @else
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                <flux:icon.check-circle class="size-10 text-green-500 mx-auto mb-3" />
                <p class="text-zinc-600 dark:text-zinc-400">No assets currently under maintenance</p>
            </div>
        @endif
    </div>

    {{-- ============================================== --}}
    {{-- SECTION 3: BORROWED ASSETS --}}
    {{-- ============================================== --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <flux:icon.arrow-right-circle class="size-5 text-blue-600 dark:text-blue-400" />
                <flux:heading size="lg">Borrowed Assets</flux:heading>
                @if($borrowedAssets->count() > 0)
                    <flux:badge color="blue">{{ $borrowedAssets->count() }} active</flux:badge>
                @endif
            </div>
            <a href="{{ route('asset-loans.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                View all
            </a>
        </div>

        @if($borrowedAssets->count() > 0)
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Asset Code</flux:table.column>
                        <flux:table.column>Asset Name</flux:table.column>
                        <flux:table.column>Borrower</flux:table.column>
                        <flux:table.column>Loan Date</flux:table.column>
                        <flux:table.column>Expected Return</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($borrowedAssets as $loan)
                            <flux:table.row>
                                <flux:table.cell class="font-mono text-sm">
                                    <a href="{{ route('assets.show', $loan->asset) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $loan->asset->asset_code }}
                                    </a>
                                </flux:table.cell>
                                <flux:table.cell>{{ $loan->asset->name }}</flux:table.cell>
                                <flux:table.cell>{{ $loan->borrower->name ?? 'Unknown' }}</flux:table.cell>
                                <flux:table.cell>{{ $loan->loan_date?->format('d M Y') ?? 'N/A' }}</flux:table.cell>
                                <flux:table.cell>
                                    @if($loan->expected_return_date)
                                        <span class="{{ $loan->isOverdue() ? 'text-red-600 dark:text-red-400 font-medium' : '' }}">
                                            {{ $loan->expected_return_date->format('d M Y') }}
                                            @if($loan->isOverdue())
                                                <flux:badge color="red" size="sm" class="ml-1">Overdue</flux:badge>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-zinc-400">Not set</span>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        @else
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                <flux:icon.check-circle class="size-10 text-green-500 mx-auto mb-3" />
                <p class="text-zinc-600 dark:text-zinc-400">No assets currently borrowed</p>
            </div>
        @endif
    </div>

    {{-- ============================================== --}}
    {{-- SECTION 4: PENDING MAINTENANCE REQUESTS --}}
    {{-- ============================================== --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <flux:icon.clipboard-document-list class="size-5 text-orange-600 dark:text-orange-400" />
                <flux:heading size="lg">Pending Maintenance Requests</flux:heading>
                @if($pendingRequests->count() > 0)
                    <flux:badge color="orange">{{ $pendingRequests->count() }} pending</flux:badge>
                @endif
            </div>
            <a href="{{ route('maintenance.requests.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                View all
            </a>
        </div>

        @if($pendingRequests->count() > 0)
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Asset Code</flux:table.column>
                        <flux:table.column>Request Date</flux:table.column>
                        <flux:table.column>Requested By</flux:table.column>
                        <flux:table.column>Issue Summary</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($pendingRequests as $request)
                            <flux:table.row>
                                <flux:table.cell class="font-mono text-sm">
                                    <a href="{{ route('assets.show', $request->asset) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $request->asset->asset_code }}
                                    </a>
                                </flux:table.cell>
                                <flux:table.cell>{{ $request->request_date?->format('d M Y') ?? 'N/A' }}</flux:table.cell>
                                <flux:table.cell>{{ $request->requester->name ?? 'Unknown' }}</flux:table.cell>
                                <flux:table.cell class="max-w-xs truncate">
                                    {{ Str::limit($request->issue_description, 50) }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge color="yellow">Pending</flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        @else
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                <flux:icon.check-circle class="size-10 text-green-500 mx-auto mb-3" />
                <p class="text-zinc-600 dark:text-zinc-400">No pending maintenance requests</p>
            </div>
        @endif
    </div>

    {{-- ============================================== --}}
    {{-- SECTIONS 5 & 6: RECENT INSPECTIONS & PROCUREMENTS --}}
    {{-- ============================================== --}}
    <div class="grid md:grid-cols-2 gap-6">
        {{-- Recent Inspections --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <flux:icon.clipboard-document-check class="size-5 text-purple-600 dark:text-purple-400" />
                    <flux:heading size="lg">Recent Inspections</flux:heading>
                </div>
                <a href="{{ route('inspections.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                    View all
                </a>
            </div>

            @if($recentInspections->count() > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($recentInspections as $inspection)
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex-1">
                                    <a href="{{ route('assets.show', $inspection->asset) }}" class="font-mono text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $inspection->asset->asset_code }}
                                    </a>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                        {{ $inspection->inspected_at?->format('d M Y H:i') ?? 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    @switch($inspection->condition_after)
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
                                            <flux:badge color="zinc">{{ $inspection->condition_after }}</flux:badge>
                                    @endswitch
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                    <flux:icon.clipboard-document-check class="size-10 text-zinc-300 dark:text-zinc-600 mx-auto mb-3" />
                    <p class="text-zinc-600 dark:text-zinc-400">No recent inspections</p>
                </div>
            @endif
        </div>

        {{-- Recent Procurements --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <flux:icon.shopping-cart class="size-5 text-teal-600 dark:text-teal-400" />
                    <flux:heading size="lg">Recent Procurements</flux:heading>
                </div>
                <a href="{{ route('procurements') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                    View all
                </a>
            </div>

            @if($recentProcurements->count() > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($recentProcurements as $procurement)
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <a href="{{ route('procurements.detail', $procurement->id) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ Str::limit($procurement->name, 30) }}
                                    </a>
                                    <span class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $procurement->quantity }} units
                                    </span>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $procurement->procurement_date?->format('d M Y') ?? 'N/A' }}
                                    </p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        Rp {{ number_format($procurement->total_cost, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                    <flux:icon.shopping-cart class="size-10 text-zinc-300 dark:text-zinc-600 mx-auto mb-3" />
                    <p class="text-zinc-600 dark:text-zinc-400">No recent procurements</p>
                </div>
            @endif
        </div>
    </div>
</div>
