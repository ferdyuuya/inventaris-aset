<x-layouts.app title="Employees">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="/" icon="home" />
            <flux:breadcrumbs.item>Employees</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <livewire:employee-manager />
    </x-page-container>
</x-layouts.app>