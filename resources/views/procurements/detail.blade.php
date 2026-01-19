<x-layouts.app title="{{ $procurement->name }}">
    <x-page-container>
        <x-breadcrumbs>
            <x-breadcrumbs.item icon="home" href="{{ route('dashboard') }}" />
            <x-breadcrumbs.item href="{{ route('procurements') }}">{{ __('Procurements') }}</x-breadcrumbs.item>
            <x-breadcrumbs.item current>{{ $procurement->name }}</x-breadcrumbs.item>
        </x-breadcrumbs>

        @livewire('procurement-detail', ['id' => $procurement->id])
    </x-page-container>
</x-layouts.app>
