<x-layouts.app title="Asset Maintenances">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item>Asset Maintenances</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @livewire('maintenance.asset-maintenances-manager')
    </x-page-container>
</x-layouts.app>
