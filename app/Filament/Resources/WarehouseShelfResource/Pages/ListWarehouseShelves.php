<?php

namespace App\Filament\Resources\WarehouseShelfResource\Pages;

use App\Filament\Resources\WarehouseShelfResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseShelves extends ListRecords
{
    protected static string $resource = WarehouseShelfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
