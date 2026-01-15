<x-layouts.app :title="__('User Management')">
    {{-- Breadcrumbs --}}
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="/" icon="home" />
        <flux:breadcrumbs.item>Users</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="mt-4">
        <livewire:user-manager />
    </div>
</x-layouts.app>