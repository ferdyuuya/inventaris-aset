<x-layouts.app title="Locations">
    <x-page-container>
        <x-breadcrumbs>
            <x-breadcrumbs.item icon="home" href="{{ route('dashboard') }}" />
            <x-breadcrumbs.item current>{{ __('Locations') }}</x-breadcrumbs.item>
        </x-breadcrumbs>

        @livewire('location-manager')
    </x-page-container>
</x-layouts.app>
