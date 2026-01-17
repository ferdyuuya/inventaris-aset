@props(['title' => null])

<div class="space-y-4 p-4 md:p-6 lg:p-8">
    @if ($title)
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
        </div>
    @endif

    {{ $slot }}
</div>
