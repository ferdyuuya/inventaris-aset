<x-layouts.app title="Assets">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item>Assets</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @livewire('assets.asset-index')
    </x-page-container>
</x-layouts.app>
