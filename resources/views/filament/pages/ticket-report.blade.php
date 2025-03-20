<div>
    <x-filament-panels::page>
        <div class="space-y-6">
            {{-- Report Options --}}
            <x-filament::section>
                <x-slot name="heading">Report Options</x-slot>

                <form wire:submit="filter" class="space-y-6">
                    {{ $this->form }}

                    <div class="flex justify-end gap-3">
                        <x-filament::button type="submit">
                            Preview
                        </x-filament::button>
                        <x-filament::button color="success" wire:click="generatePDF" wire:loading.attr="disabled"
                            wire:target="generatePDF">
                            <x-filament::icon name="heroicon-m-document" class="w-4 h-4 mr-1" />
                            Export PDF
                        </x-filament::button>
                        <x-filament::button color="warning" wire:click="generateExcel" wire:loading.attr="disabled"
                            wire:target="generateExcel">
                            <x-filament::icon name="heroicon-m-table-cells" class="w-4 h-4 mr-1" />
                            Export Excel
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>

            {{-- Preview Section --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between">
                        <span>Report Preview</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ now()->format('F d, Y') }}</span>
                    </div>
                </x-slot>

                {{-- Ticket List --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700">
                                <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">ID</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Title</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Priority
                                </th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Category
                                </th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Requestor
                                </th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Created
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->previewData['tickets'] as $ticket)
                                                        <tr>
                                                            <td class="px-4 py-2 text-gray-900 dark:text-white">#{{ $ticket->id }}</td>
                                                            <td class="px-4 py-2 text-gray-900 dark:text-white">{{ $ticket->title }}</td>
                                                            <td class="px-4 py-2">
                                                                <span @class([
                                                                    'px-2 py-1 rounded-full text-xs font-medium',
                                                                    'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' => $ticket->status === 'open',
                                                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' => $ticket->status === 'in_progress',
                                                                    'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' => $ticket->status === 'resolved',
                                                                    'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' => $ticket->status === 'completed',
                                                                    'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' => $ticket->status === 'cancelled',
                                                                ])>
                                                                    {{ strtoupper($ticket->status) }}
                                                                </span>
                                                            </td>
                                                            <td class="px-4 py-2">
                                                                <span @class([
                                                                    'px-2 py-1 rounded-full text-xs font-medium',
                                                                    'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' => $ticket->priority === 'high',
                                                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' => $ticket->priority === 'medium',
                                                                    'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' => $ticket->priority === 'low',
                                                                ])>
                                                                    {{ strtoupper($ticket->priority) }}
                                                                </span>
                                                            </td>
                                                            <td class="px-4 py-2 text-gray-900 dark:text-white">{{ $ticket->category->name }}</td>
                                                            <td class="px-4 py-2 text-gray-900 dark:text-white">
                                                                {{ $ticket->requestor->name }}
                                                            </td>
                                                            <td class="px-4 py-2 text-gray-900 dark:text-white">
                                                                {{ $ticket->created_at->format('M d, Y') }}
                                                            </td>
                                                        </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
    </x-filament-panels::page>
</div>