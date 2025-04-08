@php
    $record = $getRecord();
    $transfers = $record->warehouseTransfers()->latest()->take(5)->get();
@endphp

<div class="space-y-4">
    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Recent Transfers</div>

    @if($transfers->isEmpty())
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg text-center text-gray-500 dark:text-gray-400">
            No transfer history available
        </div>
    @else
        <div class="overflow-x-auto">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($transfers as $transfer)
                    <li class="py-3">
                        <div class="flex items-center">
                            @if($transfer->status === 'pending')
                                <span class="flex-shrink-0 w-2 h-2 bg-warning-500 rounded-full mr-2"></span>
                            @else
                                <span class="flex-shrink-0 w-2 h-2 bg-success-500 rounded-full mr-2"></span>
                            @endif

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $transfer->from_location }} → {{ $transfer->to_location }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $transfer->transfer_date ? $transfer->transfer_date->format('M d, Y g:i A') : 'N/A' }}
                                    </p>
                                </div>
                                <div class="flex justify-between">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        ({{ $transfer->fromShelf?->location?->building?->name ?? 'Unknown' }}) →
                                        ({{ $transfer->toShelf?->location?->building?->name ?? 'Unknown' }})
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        @if($transfer->status === 'completed')
                                            Completed:
                                            {{ $transfer->received_date ? $transfer->received_date->format('M d, Y g:i A') : 'N/A' }}
                                        @else
                                            <span class="text-warning-500 dark:text-warning-400">Pending</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>