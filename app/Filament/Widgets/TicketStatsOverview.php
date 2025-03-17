<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $baseQuery = auth()->user()->hasPermission('tickets.view.all')
            ? Ticket::query()
            : Ticket::where('requestor_id', auth()->id());

        return [
            Stat::make('Total Tickets', $baseQuery->count())
                ->description('All tickets')
                ->descriptionIcon('heroicon-m-ticket')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),
            Stat::make('Open Tickets', (clone $baseQuery)->where('status', 'open')->count())
                ->description('Awaiting assignment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Completed', (clone $baseQuery)->where('status', 'completed')->count())
                ->description('Successfully resolved')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
