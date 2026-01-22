<x-layouts.app title="Asset Summary">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item href="{{ route('assets.index') }}">Assets</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Summary</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @livewire('assets.asset-summary')
    </x-page-container>
</x-layouts.app>
