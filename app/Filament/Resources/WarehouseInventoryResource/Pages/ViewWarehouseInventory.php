<?php

namespace App\Filament\Resources\WarehouseInventoryResource\Pages;

use App\Filament\Resources\WarehouseInventoryResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Concerns\InteractsWithFormActions;

class ViewWarehouseInventory extends ViewRecord implements HasActions
{
    use InteractsWithActions;
    use InteractsWithFormActions;

    protected static string $resource = WarehouseInventoryResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('transfer')
                ->icon('heroicon-m-arrow-path-rounded-square')
                ->visible(fn() => !$this->record->hasPendingTransfer())
                ->form([
                    Forms\Components\Select::make('to_location')
                        ->label('Transfer to Location')
                        ->options(function () {
                            $shelves = \App\Models\WarehouseShelf::with(['location.building'])->get();
                            return $shelves->mapWithKeys(function ($shelf) {
                                $buildingName = $shelf->location->building->name ?? 'Unknown';
                                return [$shelf->location_code => "{$shelf->location_code} ({$buildingName})"];
                            })->toArray();
                        })
                        ->required()
                        ->searchable(),
                    Forms\Components\DatePicker::make('transfer_date')
                        ->required()
                        ->default(now()),
                    Forms\Components\Textarea::make('notes')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    if ($this->record->actual_count <= 0) {
                        Notification::make()
                            ->danger()
                            ->title('Transfer Failed')
                            ->body('Cannot transfer items with zero or negative actual count.')
                            ->send();
                        return;
                    }

                    \App\Models\WarehouseTransfer::create([
                        'inventory_id' => $this->record->id,
                        'from_location' => $this->record->location_code,
                        'to_location' => $data['to_location'],
                        'quantity' => $this->record->actual_count,
                        'transfer_date' => $data['transfer_date'],
                        'notes' => $data['notes'] ?? null,
                        'status' => 'pending',
                        'received_date' => null,
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Transfer Initiated')
                        ->body('Transfer has been created and is pending reception.')
                        ->send();

                    $this->getRecord(true);
                    $this->dispatch('refresh'); // Add dispatch refresh event
                }),

            Actions\Action::make('receive')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->visible(fn() => $this->record->hasPendingTransfer())
                ->requiresConfirmation()
                ->action(function (): void {
                    $transfer = $this->record->warehouseTransfers()
                        ->where('status', 'pending')
                        ->first();

                    if (!$transfer) {
                        Notification::make()
                            ->danger()
                            ->title('Receive Failed')
                            ->body('No pending transfer found.')
                            ->send();
                        return;
                    }

                    $transfer->update([
                        'status' => 'completed',
                        'received_date' => now(),
                    ]);

                    $this->record->update([
                        'location_code' => $transfer->to_location
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Transfer Completed')
                        ->body('Items have been received at the new location.')
                        ->send();

                    $this->getRecord(true);
                    $this->dispatch('refresh'); // Add dispatch refresh event
                }),
        ];
    }
}
