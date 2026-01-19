<x-layouts.app title="Maintenance Requests">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item>Maintenance Requests</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @livewire('maintenance.maintenance-requests-manager')
    </x-page-container>
</x-layouts.app>
