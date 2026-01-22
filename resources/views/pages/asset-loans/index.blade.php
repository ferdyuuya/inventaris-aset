<x-layouts.app title="Asset Loans">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item>Asset Loans</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @livewire('asset-loans.asset-loan-index')
    </x-page-container>
</x-layouts.app>
