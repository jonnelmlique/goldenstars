<div class="space-y-6 p-4">
    {{-- Title Section --}}
    <div class="border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-900">{{ $ticket->title }}</h2>
    </div>

    {{-- Status & Priority Section --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <div class="text-sm font-medium text-gray-500 mb-1">Status</div>
            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                {{ match ($ticket->status) {
    'open' => 'bg-blue-100 text-blue-800',
    'in_progress' => 'bg-yellow-100 text-yellow-800',
    'resolved' => 'bg-green-100 text-green-800',
    'completed' => 'bg-purple-100 text-purple-800',
    'cancelled' => 'bg-red-100 text-red-800',
    default => 'bg-gray-100 text-gray-800',
} }}">
                {{ strtoupper($ticket->status) }}
            </div>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500 mb-1">Priority</div>
            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                {{ match ($ticket->priority) {
    'high' => 'bg-red-100 text-red-800',
    'medium' => 'bg-yellow-100 text-yellow-800',
    'low' => 'bg-green-100 text-green-800',
    default => 'bg-gray-100 text-gray-800',
} }}">
                {{ strtoupper($ticket->priority) }}
            </div>
        </div>
    </div>

    {{-- Details Grid --}}
    <div class="grid grid-cols-2 gap-6">
        <div>
            <div class="text-sm font-medium text-gray-500 mb-1">Category</div>
            <div class="text-base">{{ $ticket->category->name }}</div>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500 mb-1">Building</div>
            <div class="text-base">{{ $ticket->building->name }}</div>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500 mb-1">Department</div>
            <div class="text-base">{{ $ticket->department->name }}</div>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500 mb-1">Requestor</div>
            <div class="text-base">{{ $ticket->requestor->name }}</div>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500 mb-1">Assignee</div>
            <div class="text-base">{{ $ticket->assignee?->name ?? 'Unassigned' }}</div>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500 mb-1">Created</div>
            <div class="text-base">{{ $ticket->created_at->format('M d, Y h:i A') }}</div>
        </div>
    </div>

    {{-- Description Section --}}
    <div class="bg-gray-50 rounded-lg p-4">
        <div class="text-sm font-medium text-gray-500 mb-2">Description</div>
        <div class="text-base whitespace-pre-line text-gray-500">{{ $ticket->description }}</div>
    </div>

    {{-- Rating Section --}}
    @if($ticket->rating)
        <div class="border-t pt-6">
            <div class="mb-4">
                <div class="text-sm font-medium text-gray-500 mb-1">Rating</div>
                <div class="text-xl">{!! str_repeat('⭐', $ticket->rating->rating) !!}</div>
            </div>

            @if($ticket->rating->comment)
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500 mb-2">Feedback</div>
                    <div class="text-base italic text-gray-500">{{ $ticket->rating->comment }}</div>
                </div>
            @endif
        </div>
    @endif
</div>