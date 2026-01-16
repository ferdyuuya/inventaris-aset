<x-layouts.app title="Procurements">
    <div class="space-y-6 p-6 md:p-8">
        <x-breadcrumbs>
            <x-breadcrumbs.item icon="home" href="{{ route('dashboard') }}" />
            <x-breadcrumbs.item current>{{ __('Procurements') }}</x-breadcrumbs.item>
        </x-breadcrumbs>

        @livewire('procurement-manager')
    </div>
</x-layouts.app>
