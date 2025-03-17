<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TicketsChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Tickets per Day';

    protected function getData(): array
    {
        $tickets = auth()->user()->hasPermission('tickets.view.all')
            ? Ticket::query()
            : Ticket::where('requestor_id', auth()->id());

        $data = $tickets->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Tickets',
                    'data' => array_values($data),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#36A2EB',
                ],
            ],
            'labels' => array_map(function ($date) {
                return Carbon::parse($date)->format('M d');
            }, array_keys($data)),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
