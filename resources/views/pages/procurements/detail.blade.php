<x-layouts.app title="{{ $procurement->name }}">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item href="{{ route('procurements') }}">Procurements</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $procurement->name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @livewire('procurements.procurement-detail', ['id' => $procurement->id])
    </x-page-container>
</x-layouts.app>
