<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <h3 class="font-medium">Item Details</h3>
            <p>Number: {{ $record->inventory->item_number }}</p>
            <p>Name: {{ $record->inventory->item_name }}</p>
        </div>
        <div>
            <h3 class="font-medium">Transfer Details</h3>
            <p>From: {{ $record->from_location }}</p>
            <p>To: {{ $record->to_location }}</p>
            <p>Quantity: {{ $record->quantity }}</p>
            <p>Date: {{ $record->transfer_date->format('M d, Y h:i A') }}</p>
        </div>
    </div>
    @if($record->notes)
        <div>
            <h3 class="font-medium">Notes</h3>
            <p>{{ $record->notes }}</p>
        </div>
    @endif
</div>