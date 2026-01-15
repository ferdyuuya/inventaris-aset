<x-layouts.app title="Suppliers">
    <div class="space-y-6 p-6 md:p-8">
        <x-breadcrumbs>
            <x-breadcrumbs.item icon="home" href="{{ route('dashboard') }}" />
            <x-breadcrumbs.item current>{{ __('Suppliers') }}</x-breadcrumbs.item>
        </x-breadcrumbs>

        @livewire('supplier-manager')
    </div>
</x-layouts.app>
