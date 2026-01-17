<x-layouts.app title="Asset Categories">
    <x-page-container>
        <x-breadcrumbs>
            <x-breadcrumbs.item icon="home" href="{{ route('dashboard') }}" />
            <x-breadcrumbs.item current>{{ __('Asset Categories') }}</x-breadcrumbs.item>
        </x-breadcrumbs>

        @livewire('asset-category-manager')
    </x-page-container>
</x-layouts.app>
