<x-layouts.app :title="__('Dashboard')">
    <x-page-container>
        {{-- Breadcrumbs --}}
        <flux:breadcrumbs>
            <flux:breadcrumbs.item icon="home" />
            <flux:breadcrumbs.item>Dashboard</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @livewire('dashboard')
    </x-page-container>
</x-layouts.app>

