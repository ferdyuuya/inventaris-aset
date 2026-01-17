<x-layouts.app title="Procurements">
    <x-page-container>
        <x-breadcrumbs>
            <x-breadcrumbs.item icon="home" href="{{ route('dashboard') }}" />
            <x-breadcrumbs.item current>{{ __('Procurements') }}</x-breadcrumbs.item>
        </x-breadcrumbs>

        @livewire('procurement-manager')
    </x-page-container>
</x-layouts.app>
