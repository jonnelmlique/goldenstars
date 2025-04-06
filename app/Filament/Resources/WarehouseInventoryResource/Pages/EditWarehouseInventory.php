<?php

namespace App\Filament\Resources\WarehouseInventoryResource\Pages;

use App\Filament\Resources\WarehouseInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditWarehouseInventory extends EditRecord
{
    protected static string $resource = WarehouseInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Inventory updated')
            ->body('The inventory has been updated successfully.');
    }
}
