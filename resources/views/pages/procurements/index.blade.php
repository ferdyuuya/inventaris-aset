<x-layouts.app title="Procurements">
    <x-page-container>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item>Procurements</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @livewire('procurements.procurement-index')
    </x-page-container>
</x-layouts.app>
