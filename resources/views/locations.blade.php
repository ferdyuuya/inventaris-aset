<x-layouts.app title="Locations">
    <div class="space-y-6 p-6 md:p-8">
        <x-breadcrumbs>
            <x-breadcrumbs.item icon="home" href="{{ route('dashboard') }}" />
            <x-breadcrumbs.item current>{{ __('Locations') }}</x-breadcrumbs.item>
        </x-breadcrumbs>

        @livewire('location-manager')
    </div>
</x-layouts.app>
