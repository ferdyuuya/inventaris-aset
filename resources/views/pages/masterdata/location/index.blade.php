<x-layouts.app title="Locations">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item>Locations</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @livewire('master-data.location-manager')
    </x-page-container>
</x-layouts.app>
