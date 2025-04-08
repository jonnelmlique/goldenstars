@php
    $record = $getRecord();
    $barcodeImage = $record->getBarcodeImage(2, 100);
@endphp

<div
    class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
    <div class="mb-2 text-xl font-bold text-primary-600 dark:text-primary-500">
        {{ $record->item_number }}
    </div>

    <div class="bg-white p-3 rounded-lg inline-block">
        <img src="{{ $barcodeImage }}" alt="{{ $record->item_number }}" class="max-w-full h-auto">
    </div>

    <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">
        Last updated: {{ $record->updated_at->diffForHumans() }}
    </div>
</div>