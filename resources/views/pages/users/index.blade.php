<x-layouts.app title="User Management">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item>Users</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @livewire('user-management.user-manager')
    </x-page-container>
</x-layouts.app>
