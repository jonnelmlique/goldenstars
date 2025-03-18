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
                        <span class="text-sm text-gray-500">{{ now()->format('F d, Y') }}</span>
                    </div>
                </x-slot>

                {{-- Ticket List --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left font-medium text-gray-500">ID</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">Title</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">Priority</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">Category</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">Requestor</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($this->previewData['tickets'] as $ticket)
                                                        <tr class="hover:bg-gray-50">
                                                            <td class="px-4 py-2">#{{ $ticket->id }}</td>
                                                            <td class="px-4 py-2">{{ $ticket->title }}</td>
                                                            <td class="px-4 py-2">
                                                                <span @class([
                                                                    'px-2 py-1 rounded-full text-xs font-medium',
                                                                    'bg-blue-100 text-blue-700' => $ticket->status === 'open',
                                                                    'bg-yellow-100 text-yellow-700' => $ticket->status === 'in_progress',
                                                                    'bg-green-100 text-green-700' => $ticket->status === 'resolved',
                                                                    'bg-purple-100 text-purple-700' => $ticket->status === 'completed',
                                                                    'bg-red-100 text-red-700' => $ticket->status === 'cancelled',
                                                                ])>
                                                                    {{ strtoupper($ticket->status) }}
                                                                </span>
                                                            </td>
                                                            <td class="px-4 py-2">
                                                                <span @class([
                                                                    'px-2 py-1 rounded-full text-xs font-medium',
                                                                    'bg-red-100 text-red-700' => $ticket->priority === 'high',
                                                                    'bg-yellow-100 text-yellow-700' => $ticket->priority === 'medium',
                                                                    'bg-green-100 text-green-700' => $ticket->priority === 'low',
                                                                ])>
                                                                    {{ strtoupper($ticket->priority) }}
                                                                </span>
                                                            </td>
                                                            <td class="px-4 py-2">{{ $ticket->category->name }}</td>
                                                            <td class="px-4 py-2">{{ $ticket->requestor->name }}</td>
                                                            <td class="px-4 py-2">{{ $ticket->created_at->format('M d, Y') }}</td>
                                                        </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
    </x-filament-panels::page>
</div>