<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <div class="text-sm font-medium text-gray-500">Title</div>
            <div>{{ $ticket->title }}</div>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500">Status</div>
            <div>{{ $ticket->status }}</div>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500">Priority</div>
            <div>{{ $ticket->priority }}</div>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500">Category</div>
            <div>{{ $ticket->category->name }}</div>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500">Requestor</div>
            <div>{{ $ticket->requestor->name }}</div>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500">Assignee</div>
            <div>{{ $ticket->assignee?->name ?? 'Unassigned' }}</div>
        </div>
    </div>

    <div>
        <div class="text-sm font-medium text-gray-500">Description</div>
        <div class="mt-1">{{ $ticket->description }}</div>
    </div>

    @if($ticket->rating)
        <div class="border-t pt-4">
            <div class="text-sm font-medium text-gray-500">Rating</div>
            <div class="mt-1">{!! str_repeat('⭐', $ticket->rating->rating) !!}</div>

            <div class="text-sm font-medium text-gray-500 mt-2">Feedback</div>
            <div class="mt-1">{{ $ticket->rating->comment }}</div>
        </div>
    @endif
</div>