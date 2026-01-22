<x-layouts.app title="Suppliers">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item>Suppliers</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @livewire('master-data.supplier-manager')
    </x-page-container>
</x-layouts.app>
