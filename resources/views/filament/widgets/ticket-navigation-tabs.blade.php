<x-filament-widgets::widget>
    <x-filament::tabs>
        <x-filament::tabs.item href="{{ route('filament.app.resources.tickets.index') }}"
            :active="request()->routeIs('filament.app.resources.tickets.index')">
            All Tickets
        </x-filament::tabs.item>
        <x-filament::tabs.item
            href="{{ route('filament.app.resources.tickets.index', ['tableFilters' => ['status' => 'open']]) }}"
            :active="request()->input('tableFilters.status') === 'open'">
            Open
        </x-filament::tabs.item>
        <x-filament::tabs.item
            href="{{ route('filament.app.resources.tickets.index', ['tableFilters' => ['status' => 'in_progress']]) }}"
            :active="request()->input('tableFilters.status') === 'in_progress'">
            In Progress
        </x-filament::tabs.item>
        <x-filament::tabs.item
            href="{{ route('filament.app.resources.tickets.index', ['tableFilters' => ['status' => 'resolved']]) }}"
            :active="request()->input('tableFilters.status') === 'resolved'">
            Resolved
        </x-filament::tabs.item>
        <x-filament::tabs.item
            href="{{ route('filament.app.resources.tickets.index', ['tableFilters' => ['status' => 'completed']]) }}"
            :active="request()->input('tableFilters.status') === 'completed'">
            Completed
        </x-filament::tabs.item>
    </x-filament::tabs>
</x-filament-widgets::widget>