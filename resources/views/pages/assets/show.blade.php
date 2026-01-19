<x-layouts.app title="Asset Detail - {{ $asset->name }}">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item href="{{ route('assets.index') }}">Assets</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $asset->asset_code }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @livewire('assets.asset-detail', ['asset' => $asset])
    </x-page-container>
</x-layouts.app>
