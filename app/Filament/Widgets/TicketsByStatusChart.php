<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\ChartWidget;

class TicketsByStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Tickets by Status';
    protected int|string|array $columnSpan = '1';

    protected function getData(): array
    {
        $tickets = auth()->user()->hasPermission('tickets.view.all')
            ? Ticket::query()
            : Ticket::where('requestor_id', auth()->id());

        $byStatus = $tickets->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $colors = [
            'open' => '#3b82f6', // blue
            'in_progress' => '#f59e0b', // yellow
            'resolved' => '#10b981', // green
            'completed' => '#8b5cf6', // purple
            'cancelled' => '#ef4444', // red
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Status',
                    'data' => array_values($byStatus),
                    'backgroundColor' => array_map(fn($status) => $colors[$status], array_keys($byStatus)),
                ],
            ],
            'labels' => array_map('strtoupper', array_keys($byStatus)),
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Changed to doughnut for better appearance
    }
}
