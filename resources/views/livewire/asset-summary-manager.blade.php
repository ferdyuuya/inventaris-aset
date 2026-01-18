<div class="space-y-6">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="rounded-md bg-green-50 p-4 dark:bg-green-900/50">
            <div class="flex">
                <div class="flex-shrink-0">
                    <flux:icon.check-circle class="h-5 w-5 text-green-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ session('message') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Asset Summary</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Overview of all assets in the system
                </p>
            </div>
            <div class="flex gap-2">
                <flux:button wire:click="refresh" variant="ghost" icon="arrow-path">
                    Refresh
                </flux:button>
                <flux:button href="{{ route('assets.index') }}" variant="primary" icon="eye">
                    View All Assets
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Summary Metrics Cards --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Assets --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <flux:icon.archive-box class="h-8 w-8 text-blue-500" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Total Assets
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($metrics['total_assets'] ?? 0) }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900/50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('assets.index') }}" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500">
                        View all →
                    </a>
                </div>
            </div>
        </div>

        {{-- Available Assets --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <flux:icon.check-circle class="h-8 w-8 text-green-500" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Available Assets
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($metrics['available_assets'] ?? 0) }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900/50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('assets.index', ['status' => 'aktif']) }}" class="font-medium text-green-600 dark:text-green-400 hover:text-green-500">
                        View available →
                    </a>
                </div>
            </div>
        </div>

        {{-- Under Maintenance --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <flux:icon.wrench-screwdriver class="h-8 w-8 text-yellow-500" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Under Maintenance
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($metrics['under_maintenance'] ?? 0) }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900/50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('assets.index', ['status' => 'dipelihara']) }}" class="font-medium text-yellow-600 dark:text-yellow-400 hover:text-yellow-500">
                        View maintenance →
                    </a>
                </div>
            </div>
        </div>

        {{-- Currently Borrowed --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <flux:icon.arrow-right-circle class="h-8 w-8 text-purple-500" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Currently Borrowed
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($metrics['currently_borrowed'] ?? 0) }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900/50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('assets.index', ['status' => 'dipinjam']) }}" class="font-medium text-purple-600 dark:text-purple-400 hover:text-purple-500">
                        View borrowed →
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Breakdown Charts --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Assets by Category --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Assets by Category
                </h3>
            </div>
            <div class="p-6">
                @if(isset($metrics['by_category']) && count($metrics['by_category']) > 0)
                    <div class="space-y-4">
                        @foreach($metrics['by_category'] as $categoryName => $count)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $categoryName }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $count }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    @php
                                        $total = $metrics['total_assets'] ?? 1;
                                        $percentage = ($total > 0) ? ($count / $total) * 100 : 0;
                                    @endphp
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                        No category data available
                    </p>
                @endif
            </div>
        </div>

        {{-- Assets by Location --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Assets by Location
                </h3>
            </div>
            <div class="p-6">
                @if(isset($metrics['by_location']) && count($metrics['by_location']) > 0)
                    <div class="space-y-4">
                        @foreach($metrics['by_location'] as $locationName => $count)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $locationName }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $count }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    @php
                                        $total = $metrics['total_assets'] ?? 1;
                                        $percentage = ($total > 0) ? ($count / $total) * 100 : 0;
                                    @endphp
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                        No location data available
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Assets by Status Breakdown --}}
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Assets by Status
            </h3>
        </div>
        <div class="p-6">
            @if(isset($metrics['by_status']) && count($metrics['by_status']) > 0)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    {{-- Active/Available --}}
                    <div class="flex items-center p-4 bg-green-50 dark:bg-green-900/30 rounded-lg">
                        <div class="flex-shrink-0">
                            <flux:icon.check-circle class="h-8 w-8 text-green-500" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-green-600 dark:text-green-400">Active</p>
                            <p class="text-2xl font-semibold text-green-700 dark:text-green-300">
                                {{ $metrics['by_status']['aktif'] ?? 0 }}
                            </p>
                        </div>
                    </div>

                    {{-- Borrowed --}}
                    <div class="flex items-center p-4 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
                        <div class="flex-shrink-0">
                            <flux:icon.arrow-right-circle class="h-8 w-8 text-purple-500" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Borrowed</p>
                            <p class="text-2xl font-semibold text-purple-700 dark:text-purple-300">
                                {{ $metrics['by_status']['dipinjam'] ?? 0 }}
                            </p>
                        </div>
                    </div>

                    {{-- Under Maintenance --}}
                    <div class="flex items-center p-4 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg">
                        <div class="flex-shrink-0">
                            <flux:icon.wrench-screwdriver class="h-8 w-8 text-yellow-500" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Maintenance</p>
                            <p class="text-2xl font-semibold text-yellow-700 dark:text-yellow-300">
                                {{ $metrics['by_status']['dipelihara'] ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                    No status data available
                </p>
            @endif
        </div>
    </div>
</div>
