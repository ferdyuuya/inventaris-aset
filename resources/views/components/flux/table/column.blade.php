@php
    $align = $attributes->get('align', 'start');
    $classes = match ($align) {
        'center' => 'text-center',
        'end' => 'text-right',
        default => 'text-left',
    };
@endphp

<th {{ $attributes->class("px-6 py-3 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider {$classes}") }} data-flux-table-column>
    {{ $slot }}
</th>
