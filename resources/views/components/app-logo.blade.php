@props([
'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand class="m-auto text-lg font-bold" name="Inventaris" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square w-34 h-30 items-center justify-between flex-row-reverse">
            <x-app-logo-icon class="text-white dark:text-black" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:sidebar.brand class="m-auto text-lg font-bold" name="Inventaris" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square w-34 h-30 items-center justify-between flex-row-reverse">
            <x-app-logo-icon class="text-white dark:text-black" />
        </x-slot>
    </flux:sidebar.brand>
@endif
