<x-layouts.app title="Asset Categories">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item>Asset Categories</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @livewire('master-data.category-manager')
    </x-page-container>
</x-layouts.app>
