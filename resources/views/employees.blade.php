<x-layouts.app :title="__('Employees')">
    {{-- Breadcrumbs --}}
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="/" icon="home" />
        <flux:breadcrumbs.item>Employees</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="mt-4">
        <livewire:employee-manager />
    </div>
</x-layouts.app>