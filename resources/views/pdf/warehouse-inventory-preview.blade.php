<div class="p-4 bg-gray-900">
    <div class="text-center mb-4">
        <h2 class="text-xl font-bold text-white">Warehouse Inventory Report</h2>
        <div class="text-gray-400 text-sm">Generated on: {{ $date }}</div>
        @if($date_from || $date_to)
            <div class="text-gray-400 text-sm">
                Period: {{ $date_from ?? 'All time' }} - {{ $date_to ?? 'Present' }}
            </div>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700" style="transform: rotate(0deg); width: 100%;">
            <thead>
                <tr>
                    <th
                        class="px-3 py-2 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Item Number</th>
                    <th
                        class="px-3 py-2 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Barcode</th>
                    <th
                        class="px-3 py-2 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Item Name</th>
                    <th
                        class="px-3 py-2 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Location</th>
                    <th
                        class="px-3 py-2 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Batch Number</th>
                    <th
                        class="px-3 py-2 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        BOM Unit</th>
                    <th
                        class="px-3 py-2 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Physical Inventory</th>
                    <th
                        class="px-3 py-2 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Reserved</th>
                    <th
                        class="px-3 py-2 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Actual Count</th>
                </tr>
            </thead>
            <tbody class="bg-gray-900 divide-y divide-gray-700">
                @foreach($inventories as $item)
                    <tr class="hover:bg-gray-800">
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-300">{{ $item->item_number }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-300">
                            <img src="{{ $item->getBarcode(1, 30) }}" alt="{{ $item->item_number }}"
                                style="max-height: 30px;">
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-300">{{ $item->item_name }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-300">{{ $item->location_code }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-300">{{ $item->batch_number }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-300">{{ $item->bom_unit }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-300">{{ $item->physical_inventory }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-300">{{ $item->physical_reserved }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-300">{{ $item->actual_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>