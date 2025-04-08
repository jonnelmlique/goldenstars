<div class="flex items-start space-x-4 p-4">
    <div class="flex-shrink-0 w-1 bg-gray-200 dark:bg-gray-700 relative">
        <div class="absolute w-3 h-3 bg-primary-500 rounded-full -left-1 top-1.5"></div>
        @if (!empty($getRecord()->notes))
            <div class="h-full w-0.5 bg-gray-200 dark:bg-gray-700 mx-auto"></div>
            <div class="absolute w-3 h-3 bg-gray-400 dark:bg-gray-600 rounded-full -left-1" style="top: 90%"></div>
        @endif
    </div>

    <div class="flex-1 space-y-6">
        {{-- Transfer Request --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 space-y-2">
            <div class="font-medium text-lg text-gray-900 dark:text-white">Transfer Request</div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-gray-500 dark:text-gray-400">From Location</div>
                    <div class="dark:text-gray-200">
                        {{ $getRecord()->from_location }}
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $getRecord()->fromShelf?->location?->building?->name ?? 'Unknown Building' }}
                        </div>
                    </div>
                </div>
                <div>
                    <div class="text-gray-500 dark:text-gray-400">To Location</div>
                    <div class="dark:text-gray-200">
                        {{ $getRecord()->to_location }}
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $getRecord()->toShelf?->location?->building?->name ?? 'Unknown Building' }}
                        </div>
                    </div>
                </div>
                <div>
                    <div class="text-gray-500 dark:text-gray-400">Quantity</div>
                    <div class="dark:text-gray-200">{{ $getRecord()->quantity }}</div>
                </div>
                <div>
                    <div class="text-gray-500 dark:text-gray-400">Transfer Date</div>
                    <div class="dark:text-gray-200">{{ $getRecord()->transfer_date->format('M d, Y h:i A') }}</div>
                </div>
            </div>
        </div>

        {{-- Status --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                @php
                    $statusColor = match ($getRecord()->status) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        default => 'danger'
                    };
                    $statusIcon = match ($getRecord()->status) {
                        'completed' => 'heroicon-o-check-circle',
                        'pending' => 'heroicon-o-clock',
                        default => 'heroicon-o-x-circle'
                    };
                @endphp
                <x-filament::badge :color="$statusColor" :icon="$statusIcon">
                    {{ ucfirst($getRecord()->status) }}
                </x-filament::badge>
            </div>

            @if($getRecord()->received_date)
                <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1">
                    <x-filament::icon icon="heroicon-m-calendar-days" class="w-4 h-4" />
                    Received: {{ $getRecord()->received_date->format('M d, Y h:i A') }}
                </div>
            @endif
        </div>

        {{-- Notes (if any) --}}
        @if (!empty($getRecord()->notes))
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 text-sm">
                <div class="flex items-center gap-2 mb-3">
                    <x-filament::icon icon="heroicon-m-chat-bubble-left-ellipsis"
                        class="w-5 h-5 text-gray-400 dark:text-gray-500" />
                    <span class="font-semibold text-gray-900 dark:text-gray-100">
                        Notes
                    </span>
                </div>
                <div
                    class="pl-3 border-l-2 border-primary-500/50 dark:border-primary-500/30 prose dark:prose-invert prose-sm">
                    {{ $getRecord()->notes }}
                </div>
            </div>
        @endif
    </div>
</div>