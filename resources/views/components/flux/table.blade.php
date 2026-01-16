@php
    $paginate = $attributes->get('paginate');
    $containerClasses = $attributes->get('container:class', '');
@endphp

@if ($paginate)
    <div class="space-y-4">
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg {{ $containerClasses }}" data-flux-table>
            <table class="w-full">
                {{ $slot }}
            </table>
        </div>
        
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Showing {{ $paginate->firstItem() ?? 0 }} to {{ $paginate->lastItem() ?? 0 }} of {{ $paginate->total() }} results
            </div>
            {{ $paginate->links() }}
        </div>
    </div>
@else
    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg {{ $containerClasses }}" data-flux-table>
        <table class="w-full">
            {{ $slot }}
        </table>
    </div>
@endif

