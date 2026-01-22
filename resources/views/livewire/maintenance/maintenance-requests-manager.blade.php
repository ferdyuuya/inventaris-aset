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
    </div>

    <flux:separator />

    {{-- Search Bar and Create Button --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1 max-w-md">
            <flux:input
                wire:model.live="search"
                type="text"
                placeholder="Search by asset code, name, or requester..."
                icon="magnifying-glass"
                clearable
                class="text-gray-900 dark:text-white"
            />
        </div>

        {{-- Create Request Button --}}
        <flux:modal.trigger name="create-request-modal">
            <flux:button
                variant="filled"
                color="blue"
                icon="plus"
                class="w-full lg:w-auto"
            >
                Create Request
            </flux:button>
        </flux:modal.trigger>
    </div>

    <flux:separator />

    {{-- Maintenance Requests Table --}}
    <div class="overflow-x-auto">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Request ID</flux:table.column>
                <flux:table.column>Asset</flux:table.column>
                <flux:table.column>Requested By</flux:table.column>
                <flux:table.column>Request Date</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($requests as $request)
                    <flux:table.row>
                        <flux:table.cell class="font-mono text-sm">
                            #{{ $request->id }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $request->asset->asset_code ?? 'N/A' }}</span>
                                <span class="text-sm text-zinc-500">{{ $request->asset->name ?? 'Unknown Asset' }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-sm">{{ $request->requester->name ?? 'Unknown' }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-sm">{{ $request->request_date?->format('d M Y') ?? 'N/A' }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            @switch($request->status)
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
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex gap-2">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="eye"
                                    wire:click="viewRequest({{ $request->id }})"
                                />

                                @if($request->status === 'diajukan')
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
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-8">
                            <flux:icon.inbox class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                            <p class="text-zinc-500">No maintenance requests found</p>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $requests->links() }}
    </div>

    {{-- ============================================== --}}
    {{-- VIEW REQUEST MODAL --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.defer="showViewModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Maintenance Request Details</flux:heading>
                <flux:text class="mt-2">Request #{{ $selectedRequest?->id ?? 'N/A' }}</flux:text>
            </div>

            @if($selectedRequest)
                <div class="space-y-4">
                    <div>
                        <flux:label>Asset</flux:label>
                        <flux:field>
                            <span class="text-sm font-medium">
                                {{ $selectedRequest->asset->asset_code }} - {{ $selectedRequest->asset->name }}
                            </span>
                        </flux:field>
                    </div>

                    <div>
                        <flux:label>Requested By</flux:label>
                        <flux:field>
                            <span class="text-sm">{{ $selectedRequest->requester->name ?? 'Unknown' }}</span>
                        </flux:field>
                    </div>

                    <div>
                        <flux:label>Request Date</flux:label>
                        <flux:field>
                            <span class="text-sm">{{ $selectedRequest->request_date?->format('d M Y') ?? 'N/A' }}</span>
                        </flux:field>
                    </div>

                    <div>
                        <flux:label>Issue Description</flux:label>
                        <flux:field>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $selectedRequest->issue_description ?? 'No description' }}</p>
                        </flux:field>
                    </div>

                    <div>
                        <flux:label>Status</flux:label>
                        <flux:field>
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
                        </flux:field>
                    </div>

                    @if($selectedRequest->approver)
                        <div>
                            <flux:label>Approved By</flux:label>
                            <flux:field>
                                <span class="text-sm">{{ $selectedRequest->approver->name }}</span>
                            </flux:field>
                        </div>
                    @endif
                </div>
            @endif

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeModals">
                    Close
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============================================== --}}
    {{-- APPROVE REQUEST MODAL --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.defer="showApproveModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Approve Maintenance Request?</flux:heading>
                <flux:text class="mt-2">Are you sure you want to approve this maintenance request?</flux:text>
            </div>

            @if($selectedRequest)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <p class="text-sm text-blue-900 dark:text-blue-100">
                        <strong>{{ $selectedRequest->asset->asset_code }}</strong> - {{ $selectedRequest->asset->name }}
                    </p>
                    <p class="text-sm text-blue-700 dark:text-blue-200 mt-1">
                        This will create an asset maintenance record and update the asset status to "In Maintenance".
                    </p>
                </div>
            @endif

            <div class="flex justify-end gap-3">
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
    <flux:modal wire:model.defer="showRejectModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Reject Maintenance Request?</flux:heading>
                <flux:text class="mt-2">Are you sure you want to reject this maintenance request?</flux:text>
            </div>

            @if($selectedRequest)
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <p class="text-sm text-red-900 dark:text-red-100">
                        <strong>{{ $selectedRequest->asset->asset_code }}</strong> - {{ $selectedRequest->asset->name }}
                    </p>
                    <p class="text-sm text-red-700 dark:text-red-200 mt-1">
                        No asset maintenance record will be created.
                    </p>
                </div>
            @endif

            <div class="flex justify-end gap-3">
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
            <div>
                <flux:heading size="lg">Create Maintenance Request</flux:heading>
                <flux:text class="mt-2">Submit a new maintenance request for an asset</flux:text>
            </div>

            <div class="space-y-4">
                {{-- Asset Select --}}
                <div>
                    <flux:label for="createAssetId">Select Asset</flux:label>
                    <flux:select
                        id="createAssetId"
                        wire:model="createAssetId"
                        placeholder="Choose an asset..."
                        class="mt-2"
                    >
                        @foreach($availableAssets as $asset)
                            <option value="{{ $asset->id }}">
                                {{ $asset->asset_code }} - {{ $asset->name }}
                            </option>
                        @endforeach
                    </flux:select>
                    @error('createAssetId')
                        <flux:error class="mt-1">{{ $message }}</flux:error>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <flux:label for="createDescription">Issue Description</flux:label>
                    <flux:textarea
                        id="createDescription"
                        wire:model="createDescription"
                        placeholder="Describe the issue or reason for maintenance..."
                        rows="4"
                        class="mt-2"
                    />
                    @error('createDescription')
                        <flux:error class="mt-1">{{ $message }}</flux:error>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3">
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
