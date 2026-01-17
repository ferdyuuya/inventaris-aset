<x-layouts.app :title="__('User Management')">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="/" icon="home" />
            <flux:breadcrumbs.item>Users</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <livewire:user-manager />
    </x-page-container>
</x-layouts.app>