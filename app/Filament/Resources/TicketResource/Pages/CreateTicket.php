<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Only set default requestor if not already set
        if (!isset($data['requestor_id'])) {
            $data['requestor_id'] = auth()->id();
        }

        $data['status'] = 'open';
        return $data;
    }
}
