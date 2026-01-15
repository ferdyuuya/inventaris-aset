<x-layouts.app title="Asset Categories">
    <div class="space-y-6 p-6 md:p-8">
        <x-breadcrumbs>
            <x-breadcrumbs.item icon="home" href="{{ route('dashboard') }}" />
            <x-breadcrumbs.item current>{{ __('Asset Categories') }}</x-breadcrumbs.item>
        </x-breadcrumbs>

        @livewire('asset-category-manager')
    </div>
</x-layouts.app>
