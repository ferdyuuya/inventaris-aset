<x-layouts.app title="Asset Detail - {{ $asset->name }}">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="/" icon="home" />
            <flux:breadcrumbs.item href="{{ route('assets.index') }}">Assets</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $asset->asset_code }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <livewire:asset-detail-manager :asset="$asset" />
    </x-page-container>
</x-layouts.app>
