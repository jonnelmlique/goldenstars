<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use App\Models\Ticket;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->model(Ticket::class)
                ->modalHeading('Create Ticket')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['requestor_id'] = auth()->id();
                    $data['status'] = 'open';
                    return $data;
                }),
        ];
    }
}
