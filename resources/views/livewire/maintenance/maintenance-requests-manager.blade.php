@php
    use App\Helpers\BadgeColorHelper;
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="text-gray-900 dark:text-white">Maintenance Requests</flux:heading>
            <flux:subheading class="text-gray-600 dark:text-gray-400 mt-2">Review and manage maintenance requests submitted by users</flux:subheading>
        </div>

        {{-- Create Request Button --}}
        <flux:modal.trigger name="create-request-modal">
            <flux:button
                variant="primary"
                icon="plus"
            >
                Create Request
            </flux:button>
        </flux:modal.trigger>
    </div>

    <flux:separator />

    {{-- Search and Filter Bar --}}
    <div class="space-y-4">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
            <div class="flex-1 max-w-md">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search by asset code, name, or requester..."
                    icon="magnifying-glass"
                    clearable
                    class="text-gray-900 dark:text-white"
                />
            </div>

            {{-- Vertical Separator --}}
            <div class="hidden lg:block w-px h-8 bg-gray-300 dark:bg-gray-600"></div>

            {{-- Filter Dropdowns --}}
            <div class="flex flex-wrap gap-3">
                {{-- Status Filter --}}
                <flux:dropdown position="bottom" align="start">
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="funnel"
                        :badge="$filterStatus ?? null ? '1' : null"
                    >
                        Status
                    </flux:button>

                    <flux:menu>
                        <flux:menu.item
                            wire:click="$set('filterStatus', '')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => !($filterStatus ?? null),
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => !($filterStatus ?? null),
                            ])>
                                All Statuses
                            </span>
                        </flux:menu.item>
                        <flux:separator />
                        <flux:menu.item
                            wire:click="$set('filterStatus', 'diajukan')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => ($filterStatus ?? null) === 'diajukan',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => ($filterStatus ?? null) === 'diajukan',
                            ])>
                                Pending
                            </span>
                        </flux:menu.item>
                        <flux:menu.item
                            wire:click="$set('filterStatus', 'disetujui')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => ($filterStatus ?? null) === 'disetujui',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => ($filterStatus ?? null) === 'disetujui',
                            ])>
                                Approved
                            </span>
                        </flux:menu.item>
                        <flux:menu.item
                            wire:click="$set('filterStatus', 'selesai')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => ($filterStatus ?? null) === 'selesai',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => ($filterStatus ?? null) === 'selesai',
                            ])>
                                Completed
                            </span>
                        </flux:menu.item>
                        <flux:menu.item
                            wire:click="$set('filterStatus', 'ditolak')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => ($filterStatus ?? null) === 'ditolak',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => ($filterStatus ?? null) === 'ditolak',
                            ])>
                                Rejected
                            </span>
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                {{-- Sorting Dropdown --}}
                <flux:dropdown position="bottom" align="start">
                    <flux:button variant="ghost" size="sm" icon="arrows-up-down">
                        Sort
                    </flux:button>

                    <flux:menu>
                        <flux:text class="px-3 py-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Sort By</flux:text>
                        <flux:separator />
                        
                        <flux:menu.item wire:click="$set('sortDirection', 'desc')">
                            <span @class(['font-semibold text-blue-600 dark:text-blue-400' => ($sortDirection ?? 'desc') === 'desc'])>
                                Newest
                            </span>
                        </flux:menu.item>

                        <flux:menu.item wire:click="$set('sortDirection', 'asc')">
                            <span @class(['font-semibold text-blue-600 dark:text-blue-400' => ($sortDirection ?? 'desc') === 'asc'])>
                                Oldest
                            </span>
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                {{-- Clear Filters --}}
                @if($search || ($filterStatus ?? null))
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="x-mark"
                        wire:click="$set('search', ''); $set('filterStatus', '')"
                    >
                        Clear
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Active Filters Summary --}}
        @if($search || ($filterStatus ?? null))
            <div class="flex flex-wrap gap-2 items-center text-sm">
                <flux:text class="text-gray-600 dark:text-gray-400">Active filters:</flux:text>
                @if($search)
                    <flux:badge color="blue" size="sm">
                        Search: <strong>{{ $search }}</strong>
                    </flux:badge>
                @endif
                @if($filterStatus ?? null)
                    <flux:badge color="blue" size="sm">
                        Status: <strong>{{ $filterStatus === 'diajukan' ? 'Pending' : ($filterStatus === 'disetujui' ? 'Approved' : ($filterStatus === 'selesai' ? 'Completed' : 'Rejected')) }}</strong>
                    </flux:badge>
                @endif
            </div>
        @endif
    </div>

    <flux:separator />

    {{-- Maintenance Requests Table --}}
    <div class="overflow-x-auto">
        <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
        @if($requests->count() > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>
                    <flux:table.column>Asset</flux:table.column>
                    <flux:table.column>Requested By</flux:table.column>
                    <flux:table.column>Request Date</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($requests as $request)
                        <flux:table.row 
                            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                            wire:click="viewRequest({{ $request->id }})"
                        >
                            <flux:table.cell>
                                <flux:text size="sm" variant="subtle" class="font-mono">#{{ $request->id }}</flux:text>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <flux:text variant="strong" color="blue">{{ $request->asset->asset_code ?? 'N/A' }}</flux:text>
                                    <flux:text size="sm" class="text-zinc-500">{{ $request->asset->name ?? 'Unknown Asset' }}</flux:text>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:icon.user-circle class="size-4 text-gray-400" />
                                    <flux:text size="sm">{{ $request->requester->name ?? 'Unknown' }}</flux:text>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:icon.calendar class="size-3 text-gray-400" />
                                    <flux:text size="sm">{{ $request->request_date?->format('d M Y, H:i') ?? 'N/A' }}</flux:text>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                @switch($request->status)
                                    @case('diajukan')
                                        <flux:badge color="blue" size="sm">
                                            <flux:icon.clock class="size-3 mr-1" />
                                            Pending
                                        </flux:badge>
                                        @break
                                    @case('disetujui')
                                        <flux:badge color="yellow" size="sm">
                                            <flux:icon.check class="size-3 mr-1" />
                                            Approved
                                        </flux:badge>
                                        @break
                                    @case('selesai')
                                        <flux:badge color="green" size="sm">
                                            <flux:icon.check-circle class="size-3 mr-1" />
                                            Completed
                                        </flux:badge>
                                        @break
                                    @case('ditolak')
                                        <flux:badge color="red" size="sm">
                                            <flux:icon.x-circle class="size-3 mr-1" />
                                            Rejected
                                        </flux:badge>
                                        @break
                                @endswitch
                            </flux:table.cell>

                            <flux:table.cell onclick="event.stopPropagation()">
                                <div class="flex gap-1">
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="eye"
                                        wire:click="viewRequest({{ $request->id }})"
                                    />

                                    @if($request->status === 'diajukan' && auth()->user()->isAdmin())
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="check"
                                            class="text-green-600 dark:text-green-400"
                                            wire:click="openApproveModal({{ $request->id }})"
                                        />
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="x-mark"
                                            class="text-red-600 dark:text-red-400"
                                            wire:click="openRejectModal({{ $request->id }})"
                                        />
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <div class="text-center py-12">
                <flux:icon.document-text class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
                <flux:heading size="lg" class="mt-4 text-zinc-600 dark:text-zinc-400">No maintenance requests found</flux:heading>
                <flux:text class="mt-2 text-zinc-500">
                    @if($search || ($filterStatus ?? null))
                        Try adjusting your filters or search term.
                    @else
                        Get started by creating your first maintenance request.
                    @endif
                </flux:text>
                @if(!$search && !($filterStatus ?? null))
                    <flux:modal.trigger name="create-request-modal">
                        <flux:button variant="primary" class="mt-4" icon="plus">
                            Create First Request
                        </flux:button>
                    </flux:modal.trigger>
                @endif
            </div>
        @endif
        </div>
    </div>

    {{-- Pagination --}}
    @if($requests->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$requests" />
        </div>
    @endif

    {{-- ============================================== --}}
    {{-- VIEW REQUEST MODAL --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.defer="showViewModal" class="md:w-full md:max-w-md">
        <div class="space-y-6">
            {{-- Modal Header --}}
            <div>
                <flux:heading size="lg">Maintenance Request Details</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Request #{{ $selectedRequest?->id ?? 'N/A' }}</flux:text>
            </div>

            @if($selectedRequest)
                {{-- Asset Information Section --}}
                <div class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Asset Information</flux:label>
                    <flux:text class="font-medium">
                        {{ $selectedRequest->asset->asset_code }} - {{ $selectedRequest->asset->name }}
                    </flux:text>
                </div>

                {{-- Request Details Section --}}
                <div class="space-y-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Request Details</flux:label>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:label class="text-zinc-500 dark:text-zinc-400">Requested By</flux:label>
                            <flux:text class="text-sm mt-1">{{ $selectedRequest->requester->name ?? 'Unknown' }}</flux:text>
                        </div>
                        <div>
                            <flux:label class="text-zinc-500 dark:text-zinc-400">Request Date</flux:label>
                            <flux:text class="text-sm mt-1">{{ $selectedRequest->request_date?->format('d M Y') ?? 'N/A' }}</flux:text>
                        </div>
                    </div>

                    <div>
                        <flux:label class="text-zinc-500 dark:text-zinc-400">Issue Description</flux:label>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $selectedRequest->issue_description ?? 'No description provided' }}</p>
                    </div>
                </div>

                {{-- Status Section --}}
                <div class="space-y-3 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Status</flux:label>
                    
                    <div class="flex items-center gap-3">
                        @switch($selectedRequest->status)
                            @case('diajukan')
                                <flux:badge color="yellow">Pending</flux:badge>
                                @break
                            @case('disetujui')
                                <flux:badge color="blue">Approved</flux:badge>
                                @break
                            @case('selesai')
                                <flux:badge color="green">Completed</flux:badge>
                                @break
                            @case('ditolak')
                                <flux:badge color="red">Rejected</flux:badge>
                                @break
                        @endswitch
                        
                        @if($selectedRequest->approver)
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                by {{ $selectedRequest->approver->name }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Result & Feedback Section (only shown when request is completed) --}}
                @if($selectedRequest->status === 'selesai' && ($selectedRequest->result || $selectedRequest->feedback))
                    <div class="space-y-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                        <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Maintenance Outcome</flux:label>
                        
                        @if($selectedRequest->result)
                            <div>
                                <flux:label class="text-zinc-500 dark:text-zinc-400">Result</flux:label>
                                <div class="mt-1">
                                    @if($selectedRequest->result === 'baik')
                                        <flux:badge color="green">Baik (Good)</flux:badge>
                                    @elseif($selectedRequest->result === 'rusak')
                                        <flux:badge color="red">Rusak (Damaged)</flux:badge>
                                    @else
                                        <flux:text class="text-sm">{{ $selectedRequest->result }}</flux:text>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($selectedRequest->feedback)
                            <div>
                                <flux:label class="text-zinc-500 dark:text-zinc-400">Technical Feedback</flux:label>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $selectedRequest->feedback }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            @endif

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <flux:button variant="ghost" wire:click="closeModals">
                    Close
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============================================== --}}
    {{-- APPROVE REQUEST MODAL --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.defer="showApproveModal" class="md:w-full md:max-w-md">
        <div class="space-y-6">
            {{-- Modal Header --}}
            <div>
                <flux:heading size="lg">Approve Maintenance Request</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Confirm approval for this maintenance request</flux:text>
            </div>

            @if($selectedRequest)
                {{-- Asset Information --}}
                <div class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Asset</flux:label>
                    <flux:text class="font-medium">
                        {{ $selectedRequest->asset->asset_code }} - {{ $selectedRequest->asset->name }}
                    </flux:text>
                </div>

                {{-- Info Notice --}}
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <flux:icon.information-circle class="size-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                        <p class="text-sm text-blue-700 dark:text-blue-200">
                            This will create an asset maintenance record and update the asset status to "In Maintenance".
                        </p>
                    </div>
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <flux:button variant="ghost" wire:click="closeModals">
                    Cancel
                </flux:button>
                <flux:button
                    variant="filled"
                    color="blue"
                    wire:click="approveRequest({{ $selectedRequest?->id }})"
                >
                    Approve Request
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============================================== --}}
    {{-- REJECT REQUEST MODAL --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.defer="showRejectModal" class="md:w-full md:max-w-md">
        <div class="space-y-6">
            {{-- Modal Header --}}
            <div>
                <flux:heading size="lg">Reject Maintenance Request</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Confirm rejection for this maintenance request</flux:text>
            </div>

            @if($selectedRequest)
                {{-- Asset Information --}}
                <div class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Asset</flux:label>
                    <flux:text class="font-medium">
                        {{ $selectedRequest->asset->asset_code }} - {{ $selectedRequest->asset->name }}
                    </flux:text>
                </div>

                {{-- Warning Notice --}}
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                        <p class="text-sm text-red-700 dark:text-red-200">
                            No asset maintenance record will be created. The asset will remain in its current status.
                        </p>
                    </div>
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <flux:button variant="ghost" wire:click="closeModals">
                    Cancel
                </flux:button>
                <flux:button
                    variant="filled"
                    color="red"
                    wire:click="rejectRequest({{ $selectedRequest?->id }})"
                >
                    Reject Request
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============================================== --}}
    {{-- CREATE MAINTENANCE REQUEST MODAL --}}
    {{-- ============================================== --}}
    <flux:modal name="create-request-modal" class="md:w-full md:max-w-md" @close="$wire.closeModals()">
        <div class="space-y-6">
            {{-- Modal Header --}}
            <div>
                <flux:heading size="lg">Create Maintenance Request</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Submit a new maintenance request for an asset</flux:text>
            </div>

            {{-- Asset Selection Section --}}
            <div class="space-y-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Asset Selection</flux:label>
                
                <flux:field>
                    <flux:label for="createAssetId">Select Asset <span class="text-red-500">*</span></flux:label>
                    <flux:select
                        id="createAssetId"
                        wire:model="createAssetId"
                        placeholder="Choose an asset..."
                    >
                        @foreach($availableAssets as $asset)
                            <option value="{{ $asset->id }}">
                                {{ $asset->asset_code }} - {{ $asset->name }}
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="createAssetId" />
                </flux:field>
            </div>

            {{-- Problem Description Section --}}
            <div class="space-y-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Problem Description</flux:label>
                
                <flux:field>
                    <flux:label for="createDescription">Issue Description <span class="text-red-500">*</span></flux:label>
                    <flux:textarea
                        id="createDescription"
                        wire:model="createDescription"
                        placeholder="Describe the issue or reason for maintenance..."
                        rows="4"
                    />
                    <flux:description>Provide a clear description of the problem or maintenance needed</flux:description>
                    <flux:error name="createDescription" />
                </flux:field>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <flux:modal.close>
                    <flux:button variant="ghost">
                        Cancel
                    </flux:button>
                </flux:modal.close>
                <flux:button
                    variant="filled"
                    color="blue"
                    wire:click="submitCreateMaintenance"
                >
                    Submit Request
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
