<x-layouts.app title="Suppliers">
    <x-page-container>
        <x-breadcrumbs>
            <x-breadcrumbs.item icon="home" href="{{ route('dashboard') }}" />
            <x-breadcrumbs.item current>{{ __('Suppliers') }}</x-breadcrumbs.item>
        </x-breadcrumbs>

        @livewire('supplier-manager')
    </x-page-container>
</x-layouts.app>
