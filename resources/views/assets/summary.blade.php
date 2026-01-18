<x-layouts.app title="Asset Summary">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="/" icon="home" />
            <flux:breadcrumbs.item href="{{ route('assets.index') }}">Assets</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Summary</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <livewire:asset-summary-manager />
    </x-page-container>
</x-layouts.app>
