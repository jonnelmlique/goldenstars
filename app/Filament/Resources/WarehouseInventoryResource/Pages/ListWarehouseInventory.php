<?php

namespace App\Filament\Resources\WarehouseInventoryResource\Pages;

use App\Filament\Resources\WarehouseInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseInventory extends ListRecords
{
    protected static string $resource = WarehouseInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
