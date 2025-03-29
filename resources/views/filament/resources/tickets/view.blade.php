<div class="space-y-6">
    <div>
        {{-- Title and Status Bar --}}
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ticket->title }}</h2>
            <div class="flex items-center gap-2">
                <span @class([
                    'px-3 py-1 rounded-full text-sm font-medium',
                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' => $ticket->status === 'open',
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' => $ticket->status === 'in_progress',
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $ticket->status === 'resolved',
                    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' => $ticket->status === 'completed',
                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => $ticket->status === 'cancelled',
                ])>
                    {{ strtoupper($ticket->status) }}
                </span>
                <span @class([
                    'px-3 py-1 rounded-full text-sm font-medium',
                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => $ticket->priority === 'high',
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' => $ticket->priority === 'medium',
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $ticket->priority === 'low',
                ])>
                    {{ strtoupper($ticket->priority) }}
                </span>
            </div>
        </div>

        {{-- Metadata Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg mb-6">
            <div>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Category</div>
                <div class="flex items-center gap-1 text-gray-900 dark:text-white">
                    <x-heroicon-m-tag class="w-4 h-4 text-primary-500" />
                    {{ $ticket->category->name }}
                </div>
            </div>
            <div>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Building</div>
                <div class="flex items-center gap-1 text-gray-900 dark:text-white">
                    <x-heroicon-m-building-office class="w-4 h-4 text-primary-500" />
                    {{ $ticket->building->name }}
                </div>
            </div>
            <div>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Department</div>
                <div class="flex items-center gap-1 text-gray-900 dark:text-white">
                    <x-heroicon-m-academic-cap class="w-4 h-4 text-primary-500" />
                    {{ $ticket->department->name }}
                </div>
            </div>
            <div>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Requestor</div>
                <div class="flex items-center gap-1 text-gray-900 dark:text-white">
                    <x-heroicon-m-user class="w-4 h-4 text-primary-500" />
                    {{ $ticket->requested_by ?? $ticket->requestor->name }}
                </div>
            </div>
            <div>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Assignee</div>
                <div class="flex items-center gap-1 text-gray-900 dark:text-white">
                    <x-heroicon-m-user class="w-4 h-4 text-primary-500" />
                    {{ $ticket->assignee?->name ?? 'Unassigned' }}
                </div>
            </div>
            <div>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Requestor Department</div>
                <div class="flex items-center gap-1 text-gray-900 dark:text-white">
                    <x-heroicon-m-building-office-2 class="w-4 h-4 text-primary-500" />
                    {{ $ticket->requestor->department->name }}
                </div>
            </div>
        </div>

        {{-- Description --}}
        <div class="prose dark:prose-invert max-w-none">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Description</h3>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-gray-600 dark:text-gray-300">
                {{ $ticket->description }}
            </div>
        </div>

        {{-- Rating Section --}}
        @if($ticket->rating)
            <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-lg font-medium text-gray-900 dark:text-white">Rating</span>
                    <div class="text-yellow-400">
                        @for($i = 0; $i < $ticket->rating->rating; $i++)
                            <x-heroicon-s-star class="w-5 h-5 inline" />
                        @endfor
                    </div>
                </div>
                @if($ticket->rating->comment)
                    <p class="text-gray-600 dark:text-gray-300 italic mt-2">
                        "{{ $ticket->rating->comment }}"
                    </p>
                @endif
            </div>
        @endif

        {{-- Timeline/Updates --}}
        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Timeline</h3>
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <x-heroicon-m-clock class="w-4 h-4" />
                    Created {{ $ticket->created_at->diffForHumans() }}
                </div>
                @if($ticket->created_at != $ticket->updated_at)
                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <x-heroicon-m-arrow-path class="w-4 h-4" />
                        Updated {{ $ticket->updated_at->diffForHumans() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>