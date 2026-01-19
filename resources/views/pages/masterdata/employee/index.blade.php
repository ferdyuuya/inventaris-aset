<x-layouts.app title="Employees">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item>Employees</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @livewire('master-data.employee-manager')
    </x-page-container>
</x-layouts.app>
