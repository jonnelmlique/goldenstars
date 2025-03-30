<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Resources\Components\Tab;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Tickets')
                ->badge(Ticket::count()),
            'open' => Tab::make('Open')
                ->badge(Ticket::where('status', 'open')->count())
                ->modifyQueryUsing(fn($query) => $query->where('status', 'open')),
            'in_progress' => Tab::make('In Progress')
                ->badge(Ticket::where('status', 'in_progress')->count())
                ->modifyQueryUsing(fn($query) => $query->where('status', 'in_progress')),
            'resolved' => Tab::make('Resolved')
                ->badge(Ticket::where('status', 'resolved')->count())
                ->modifyQueryUsing(fn($query) => $query->where('status', 'resolved')),
            'completed' => Tab::make('Completed')
                ->badge(Ticket::where('status', 'completed')->count())
                ->modifyQueryUsing(fn($query) => $query->where('status', 'completed')),
        ];
    }
}
