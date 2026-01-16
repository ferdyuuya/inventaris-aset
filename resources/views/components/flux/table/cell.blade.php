@php
    $align = $attributes->get('align', 'start');
    $variant = $attributes->get('variant', 'default');
    
    $alignClasses = match ($align) {
        'center' => 'text-center',
        'end' => 'text-right',
        default => 'text-left',
    };
    
    $variantClasses = match ($variant) {
        'strong' => 'font-semibold text-gray-900 dark:text-white',
        default => 'text-gray-700 dark:text-gray-300',
    };
@endphp

<td {{ $attributes->class("px-6 py-4 whitespace-nowrap text-sm {$alignClasses} {$variantClasses}") }} data-flux-table-cell>
    {{ $slot }}
</td>
